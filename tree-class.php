<?php
class KDT{
	/**
	 *
	 * the category of facilities of the kdtree
	 **/
	var $category = 0;
	
	/**
	 *
	 * the root node
	 **/
	var $root = NULL;
	/**
	 *
	 * the retrival result of the nodes of the tree in the format of sql result
	 **/
	var $arr = array();
	function __construct( $cat, $con = 0 ) {
		if( ! ($cat >= 0) ){
			return;
		}
		$this->category = $cat;
		  if( 0 != $con ){
			  $this->init($con);
		}
	 }
	 function init( $con ){
		if (!$con)
		{
			echo "db connection error";
			retrun;
		}
		$sql = "SELECT b.child as node, b.father, b.axis, b.child_left, b.child_right, 			a.x_coord, a.y_coord
		FROM `poi_info` AS a
		JOIN poi_kdt AS b ON a.ID = b.child
		WHERE b.cat = " . $this->category ; 
		$result = mysql_query($sql,$con);
		$newarr = array();
		while( $row = mysql_fetch_assoc($result)){
			$node = new KDT_node($row);
			$newarr[$row["node"]] = $node;
			if( $row["father"] == "0" ){
				$this->root = $node;
			}
		}
		$this->arr = $newarr;
	}
	function dump(){
		echo "<pre>";
		print_r($this);
		echo "</pre>";	
	}
	/*************************************************************
	 *
	 *  Nearest search
	 *************************************************************/
	function nearest( $x, $y ){
		$path = array();
		$node = $this->root;
		array_push($path , $node );
		while( !$node->is_leaf() ){
			if( $node->checkSide( $x, $y ) < 0 ){
				if( $node->child_left == "0" ){
					break;
				}
				$node = $this->arr[$node->child_left];
			}else{
				if( $node->child_right == "0" ){
					break;
				}
				$node = $this->arr[$node->child_right];
			}
			array_push($path , $node );
		}
		$shortest = $node->distance( $x, $y);
		
		// circle
		$circle = new tCircle($x,$y,$shortest);
		// unset the last leaf node and search back
		unset($path[array_search($node,$path)]);
		
		while( sizeof( $path ) > 0 ){
			//echo "path size:" . sizeof($path) ."<br/>";
			$n = array_pop($path);
			
			if( $n->is_leaf() ){
				// check whether it's a leaf node
				//echo "leaf node<br/>";
				$nr = $circle->rCloser($n);
				if( $nr > 0 ){
					$circle->updateR($nr);
					$node = $n;
				}
				continue;	
			}
			// if it's not a leaf node
			if( $circle->checkInter( $n ) == 0 ){
				//echo "check the other side of the branch<br/>";
				// if the circle has intersection with the axis of node n
				// refresh the closest distance
				$nr = $circle->rCloser($n);
				if( $nr > 0 ){
					$circle->updateR($nr);
					$node = $n;
				}
				//	check which side the center point belongs to
				if( $n->checkSide( $circle->x,$circle->y) < 0 ){
					// if on the left then check the right
					$node2 = $n->child_right;
				}else{
					$node2 = $n->child_left;
				}
				if( $node2 == "0" ){
						continue;
				}else{
					$node2 = $this->arr[$node2];
					array_push($path , $node2 );
					// repeat the nearest checking algorithm
					while( !$node2->is_leaf() ){
						//print_r($node2);
						if( $node2->checkSide( $x, $y ) < 0 ){
							//echo "check left";
							if( $node2->child_left == "0" ){
								break;
							}
							$node2 = $this->arr[$node2->child_left];
						}else{
							//echo "check right";
							if( $node2->child_right == "0" ){
								break;
							}
							$node2 = $this->arr[$node2->child_right];
						}
						//print_r($node2);
						array_push($path , $node2 );
					}
					//print_r($node2);
					continue;
				}
			}
			//echo $circle->r . "<br/>";
			
		}
		
		//echo "shortest final -> " . $circle->r . "<br/>";
		//echo $node->distance($circle->x,$circle->y);
		return $node;
	}
	
	/*************************************************************
	 *
	 *  Range search
	 *************************************************************/
	var $cir = NULL;
	function runRange( $x, $y, $r ){
		$this->cir = new tCircle( $x, $y, $r );
		$this->runRangeSub( $this->root );
	}
	private function runRangeSub( $node ){
		if( NULL == $this->cir || !$node){
			return;
		}
		$circle = $this->cir;
		//print_r($circle);
		//print_r($node);
		//echo $node->distance($circle->x,$circle->y) . "<br/>";
		if( $node->is_leaf() ){
			if( $node->distance($circle->x,$circle->y) <= $circle->r ){
				//echo $node->ID . "<br/>";
				array_push($this->cir->fSet,$node);
			}
			return;
		}
		// if it's not a leaf node;
		// check whether the axis has a intersection with the circle
		$check = $circle->checkInter($node);
		if( $check == 0 ){
			if( $node->distance($circle->x,$circle->y) <= $circle->r ){
				array_push($this->cir->fSet,$node);
				//echo $node->ID. "<br/>";
			}
			
			//check both side
			if( $node->child_left !="0"){
				$this->runRangeSub( $this->arr[$node->child_left]);
			}
			if( $node->child_right != "0" ){
				$this->runRangeSub( $this->arr[$node->child_right]);
			}
		}elseif( $check < 0 ) {
			//no intersection, only check right side
			if( $node->child_right != "0" ){
				$this->runRangeSub( $this->arr[$node->child_right]);
			}
		}else{
			//no intersection, only check left side
			if( $node->child_left !="0"){
				$this->runRangeSub( $this->arr[$node->child_left]);
			}
		}
	}
}

class KDT_node{
	var $ID = "0";
	var $father = "0";
	var $axis = -1;
	var $child_left = "0";
	var $child_right = "0";
	var $x = 0;
	var $y = 0;
	
	function __construct( $data ) {
		$this->ID = $data["node"];
		$this->father = $data["father"];
		$this->child_left = $data["child_left"];
		$this->child_right = $data["child_right"];
		$this->x = $data["x_coord"];
		$this->y = $data["y_coord"];
		$this->axis = $data["axis"];
	}
	
	function is_leaf(){
		if( $this->axis > 0 ){
			return false;
		}
		return true;
	}
	
	// whether the node for children nodes is based on x axis
	function is_x(){
		if( $this->axis == 1 ){
			return true;
		}
		return false;
	}
	
	// whether the node for children nodes is based on y axis
	function is_y(){
		if(  $this->axis == 2 ){
			return true;
		}
		return false;
	}
	
	function is_root(){
		if( $this->father == "0"){
			return true;
		}
		else return false;
	}
	
	// check whether the target point belong to the left side or right side of given node
	function checkSide( $x, $y ){
		if( $this->axis == 1){
			//x axis	
			if( $x < $this->x ){
				return -1;
			}
			return 1;
			
		}elseif( $this->axis == 2){
			// y axis
			if( $y < $this->y ){
				return -1;
			}
			return 1;	
		}
		return 0;
	}
	function distance( $x, $y ){
		return eDistance($this->x,$this->y,$x,$y);
	}
}

class tCircle{

	var $x;
	var $y;
	var $r;
	
	var $fSet = array();
	
	function __construct( $x, $y , $d = 9999999999) {
		$this->x = $x;
		$this->y = $y;
		$this->r = $d;
	}
	
	function updateR( $newr ){
		$this->r = $newr;
	}
	
	function rCloser( $node ){
		if( ! is_a($node, "KDT_node") )
		{
			exit("input error");
		}
		$newr = eDistance( $node->x, $node->y , $this->x, $this->y);
		//echo "distance in rclose：" . $newr . "<br/>";
		if( $newr < $this->r ){
			return $newr;
		}else{
			return -1;
		}
	}
	
	function within( $node ){
		if( $this->rCloser( $node ) > 0 ){
			return true;
		}
		return false;
	}
	
	
	// whether has intersection with the axis of the given node
	// @return:
	// 	0: has intersection ; 
	// -1: no intersection and on the node's left 
	// 	1: no intersection and on the node's right 
	function checkInter( $node ){
		if( ! is_a($node, "KDT_node") )
		{
			exit("input error");
		}
		
		// the node should not be a leaf node
		if( $node->is_leaf() ){
			return NULL;
		}
		if( $node->is_x() ){
			if( eDistance($this->x,$node->y,$node->x,$node->y) < $this->r ){
				return 0;
			}else{
				if( $node->x < $this->x ){
					return -1;
				}else{
					return 1;
				}
			}
		}elseif( $node->is_y() ){
			if( eDistance($node->x,$this->y,$node->y,$node->y) < $this->r ){
				return 0;
			}else{
				if( $node->y < $this->y ){
					return -1;
				}else{
					return 1;
				}
			}
		}
	}
}
?>