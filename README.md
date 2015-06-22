# POI
> A sample for **urban computing** in big data course.
> **KD-tree** is utilized for range search and nearest search

## Task description:
###### Given 
*  A sampled POI dataset in Shanghai
*  [Bing Map SDK] which can  
    * obtain the location of a point (e.g. the red star shown in the example)
    * overlap information (including push-pins and lines) on a map


##### Build a query-answer engine
*  Two types of queries: KNN and spatial range query
*  Using any indexing and retrieval algorithms taught in the course 
*  Concerned with efficiency
*  The query location, spatial range and category of POIs are variables 


##### Build a simple interface that can 
*  Accept user-specified queries 
*  Visualize the results (a map-based view is preferred)


**The sample is a small web app writen in PHP + JavaScript, AJAX is mainly used for updating the dynamic containt**
**Details are inclued in readme.pdf**
> In data folder, origin data are in task folder, script in code-r is for processing origin data in to KD-tree structure, poi.sql contains the structed data after processed
[Bing Map SDK]:http://msdn.microsoft.com/zh-cn/library/bb429593.aspx
