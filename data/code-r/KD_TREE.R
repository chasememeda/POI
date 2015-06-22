library(RODBC);
db <- odbcConnect("poi",uid="root",pwd="root");
#info <- odbcQuery(db,"select ID, NAME, X_COORD AS X, Y_COORD AS Y , CATEGORY AS CAT  from poi_info");
cats <- sqlQuery(db,"select count(ID) as total, category from poi_info group by category ORDER BY total DESC");

#################################
#Functions
#################################
runTree<- function( cat ){
  kdt <- sqlQuery(db,paste("select ID, X_COORD AS X, Y_COORD AS Y  from poi_info WHERE CATEGORY = ",cat));
  #initialize the tree
  father <- 0;
  #####################################
  # kd-tree generation function
  #####################################
  runKdTree <- function(db,data,category,father){
    if( dim(data)[1] == 1 ){
      #leaf node
      child <- as.character(data[1,1]);
      tree<-append(tree,c(father,child));
      sql<-paste("INSERT INTO poi_kdt(cat,father,child) VALUES(",cat,",'",father,"','",child,"');",sep="")
      sqlQuery(db,sql);
      #print(sql);
    }else{
      #data <-kdt;
      varx <- var(data[,2]);
      vary <- var(data[,3]);
      cur <- 3;
      #print(c(varx,vary));
      #father <- 0;
      if(varx > vary){
        #start from x
        cur <- 2;
      }
      vector <- data[,cur];
      med <- median(x=vector);
      if( length(vector)%%2 != 0 ){
        node <- which(vector==med)[1]
      }else{
        node <- which.min(abs(vector-med))[1];
        med <- vector[node];
      }
      child <- as.character(data[node,1]);
      
      sql<-paste("INSERT INTO poi_kdt(cat,father,child,axis) VALUES(",cat,",'",father,"','",child,"',", (cur - 1),");",sep="")
      sqlQuery(db,sql);
      #print(sql);
      data1 <- data[which(vector<med),]
      data2 <- which(vector>=med);
      #in case of duplicate median value, the others are assigned to the second child
      data2 <- data2[-which(data2==node)];
      data2 <- data[data2,]
      if(!is.null(dim(data1))){
        if(dim(data1)[1]>0){
          #recursion
          runKdTree(db,data1,category,child);
        }
      }
      if(!is.null(dim(data2))){
        if(dim(data2)[1]>0){
          #recursion
          runKdTree(db,data2,category,child);
        }
      }
    }
  }
  odbcSetAutoCommit(db, autoCommit = FALSE)
  runKdTree(db,kdt,cat,father);
  odbcEndTran(db, commit = TRUE)
}
run<-function(cat){
  print(paste("total",cat[1]))
  runTree(cat[2]);
  print(paste("end",cat[2]))
}
##################################
#Scripts
##################################
sqlQuery(db,"truncate poi_kdt;");
apply(cats,1,run)