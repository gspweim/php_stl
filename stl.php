<?php
	error_reporting(E_ALL | E_STRICT);
	$fileName=$argv[1];
	$inFile = new stlFile();
	$inFile->loadFile($fileName);
	echo "Total Triangles Start " . $inFile->getNumTriangles() ."\n";
	//now lets cut
	$trianglesLeft = $inFile->filterZ(48,">");
	echo "Total Triangles Left after Z cut " . $inFile->getNumTriangles() ."\n";
	$trianglesLeft = $inFile->filterY(30,">");
	echo "Total Triangles Left after Y cut " . $inFile->getNumTriangles() ."\n";
	$inFile->writeFile("out.stl");
	

	echo "Header " . $inFile->getHeader()  ."\n";
	echo "Total Triangles " . $inFile->getNumTriangles() ."\n";
	echo "Max/Min " .  $inFile->getMaxX() . "," . $inFile->getMaxY() . "," . $inFile->getMaxZ() . " : " .  $inFile->getMinX() . "," . $inFile->getMinY() . "," . $inFile->getMinZ() . "\n";

	exit();
	class stlFile {
    		private $numTriangles;
   	 	private $triangles;
		private $header;
		private $maxX;
		private $minX;
		private $maxY;
		private $minY;
		private $maxZ;
		private $minZ;
		private $fileName;
		private $filterOn;
		
		function __construct() {
			$this->header = "";
			$this->numTriangles = 0;
			$this->triangles =  array();
			$this->maxX = 0;
			$this->minX = 99999;
			$this->maxY = 0;
			$this->minY = 99999;
			$this->maxZ = 0;
			$this->minZ = 99999;
			$this->fileName = "";
			$this->filterOn = 0;
		}

		public function getNumTriangles() {
			//no setter since adding triangle counts it
			//lets just count the ones for the current function
			$triangleCount = 0;
			foreach ($this->triangles as $triangle) {
				if($triangle->getFilterNum() == $this->filterOn) $triangleCount++;
			}
			return $triangleCount;
		}
		public function setHeader($header) {
			$this->header = $header;
		}
		public function getHeader() {
			return $this->header;
		}
		//single triangle
		public function addTriangle($nX,$nY,$nZ,
					$oneX,$oneY,$oneZ,
					$twoX,$twoY,$twoZ,
					$threeX,$threeY,$threeZ,
					$attribBytes){
			$triangle = new triangle();
			$triangle->addVertex($nX,$nY,$nZ,
					$oneX,$oneY,$oneZ,
					$twoX,$twoY,$twoZ,
					$threeX,$threeY,$threeZ,
					$attribBytes,$this->filterOn);
			$this->numTriangles++;
			$this->triangles[] = $triangle;
			$this->calcMinMax($oneX,$oneY,$oneZ);
			$this->calcMinMax($twoX,$twoY,$twoZ);
			$this->calcMinMax($threeX,$threeY,$threeZ);

		}
		//array of triangle classes
		public function addTriangles($triangles){
			//going to split them out for fun and to use the logic
			foreach ($triangles as $triangle){
				$this->addTriangle($triangle->vNormal->x,$triangle->vNormal->y,
				$triangle->vNormal->z,
				$triangle->v1->x,$triangle->v1->y,$triangle->v1->z,
				$triangle->v2->x,$triangle->v2->y,$triangle->v2->z,
				$triangle->v3->x,$triangle->v3->y,$triangle->v3->z,
				$triangle->attribBytes);
			}

		}
		private function calcMinMax($x,$y,$z){
			if($x>$this->maxX) $this->maxX = $x;
			if($x<$this->minX) $this->minX = $x;
			if($y>$this->maxY) $this->maxY = $y;
			if($y<$this->minY) $this->minY = $y;
			if($z>$this->maxZ) $this->maxZ = $z;
			if($z<$this->minZ) $this->minZ = $z;
		}
		public function getMinX(){
			return $this->minX;
		}
		public function getMaxX(){
			return $this->maxX;
		}
		public function getMinY(){
			return $this->minY;
		}
		public function getMaxY(){
			return $this->maxY;
		}
		public function getMinZ(){
			return $this->minZ;
		}
		public function getMaxZ(){
			return $this->maxZ;
		}
		public function filterZ($z,$operator ="<"){
			$lastFilter = $this->filterOn;
			$this->filterOn++;		
			$trianglesAdded = 0;	
			foreach ($this->triangles as $triangle) {
				if($triangle->getFilterNum() == $lastFilter){
					if($operator == "<"){
						if(  (($triangle->v1->z - $this->minZ) < $z) &&
							(($triangle->v2->z - $this->minZ) < $z) &&
							(($triangle->v3->z - $this->minZ) < $z) ){
							$triangle->setFilterNum($this->filterOn);
							$trianglesAdded++;
						}
					} else {
						if(  (($triangle->v1->z - $this->minZ) >= $z) &&
							(($triangle->v2->z - $this->minZ) >= $z) &&
							(($triangle->v3->z - $this->minZ) >= $z) ){
							$triangle->setFilterNum($this->filterOn);
							$trianglesAdded++;
						}
					}
				}
			}
			return $trianglesAdded;
		}
		public function filterX($x,$operator ="<"){
			$lastFilter = $this->filterOn;
			$this->filterOn++;		
			$trianglesAdded = 0;	
			foreach ($this->triangles as $triangle) {
				if($triangle->getFilterNum() == $lastFilter){
					if($operator == "<"){
						if(  (($triangle->v1->x - $this->minX) < $x) &&
							(($triangle->v2->x - $this->minX) < $x) &&
							(($triangle->v3->x - $this->minX) < $x) ){
							$triangle->setFilterNum($this->filterOn);
							$trianglesAdded++;
						}
					} else {
						if(  (($triangle->v1->x - $this->minX) >= $x) &&
							(($triangle->v2->x - $this->minX) >= $x) &&
							(($triangle->v3->x - $this->minX) >= $x) ){
							$triangle->setFilterNum($this->filterOn);
							$trianglesAdded++;
						}
					}
				}
			}
			return $trianglesAdded;
		}
		public function filterY($y,$operator ="<"){
			$lastFilter = $this->filterOn;
			$this->filterOn++;		
			$trianglesAdded = 0;	
			foreach ($this->triangles as $triangle) {
				if($triangle->getFilterNum() == $lastFilter){
					if($operator == "<"){
						if(  (($triangle->v1->y - $this->minY) < $y) &&
							(($triangle->v2->y - $this->minY) < $y) &&
							(($triangle->v3->y - $this->minY) < $y) ){
							$triangle->setFilterNum($this->filterOn);
							$trianglesAdded++;
						}
					} else {
						if(  (($triangle->v1->y - $this->minY) >= $y) &&
							(($triangle->v2->y - $this->minY) >= $y) &&
							(($triangle->v3->y - $this->minY) >= $y) ){
							$triangle->setFilterNum($this->filterOn);
							$trianglesAdded++;
						}
					}
				}
			}
			return $trianglesAdded;
		}
		public function writeFile($fileName){
			$handle = fopen($fileName,"wb");
			fwrite($handle ,$this->header, 80 );
			fwrite($handle,pack("l",$this->getNumTriangles()),4);
			foreach($this->triangles as $triangle){
				if($triangle->getFilterNum() == $this->filterOn) {
					fwrite($handle,pack("g",$triangle->vNormal->x),4);
					fwrite($handle,pack("g",$triangle->vNormal->y),4);
					fwrite($handle,pack("g",$triangle->vNormal->z),4);
					fwrite($handle,pack("g",$triangle->v1->x),4);
					fwrite($handle,pack("g",$triangle->v1->y),4);
					fwrite($handle,pack("g",$triangle->v1->z),4);
					fwrite($handle,pack("g",$triangle->v2->x),4);
					fwrite($handle,pack("g",$triangle->v2->y),4);
					fwrite($handle,pack("g",$triangle->v2->z),4);
					fwrite($handle,pack("g",$triangle->v3->x),4);
					fwrite($handle,pack("g",$triangle->v3->y),4);
					fwrite($handle,pack("g",$triangle->v3->z),4);
					fwrite($handle,pack("S",$triangle->attribBytes),2);
				}
			}
			fclose($handle);
		}
		public function loadFile($fileName){
			$this->fileName = $fileName;
			$handle = fopen($fileName,"rb");
			$this->readHeader($handle);
			$numTriangles = $this->readNumTriangles($handle);
			$this->readTriangles($handle,$numTriangles);
			fclose($handle);	
		}
		private function readHeader($handle){
			$this->header = fread ($handle , 80 );
		}

		private function readNumTriangles($handle) {
			$numTriangles = fread($handle,4);
			$uITriangles = unpack("luint",$numTriangles);			
			//dont set class triangles here in case we want to combine stl(s)
			//$this->numTriangles = $uITriangles['uint'];
			return $uITriangles['uint'];
		}
		private function readTriangles($handle,$numTriangles){
			for($x=0;$x<$numTriangles;$x++){
				$vertexNx = unpack("gfloat",fread($handle,4));
				$vertexNy = unpack("gfloat",fread($handle,4));
				$vertexNz = unpack("gfloat",fread($handle,4));
				$vertex1x = unpack("gfloat",fread($handle,4));
				$vertex1y = unpack("gfloat",fread($handle,4));
				$vertex1z = unpack("gfloat",fread($handle,4));
				$vertex2x = unpack("gfloat",fread($handle,4));
				$vertex2y = unpack("gfloat",fread($handle,4));
				$vertex2z = unpack("gfloat",fread($handle,4));
				$vertex3x = unpack("gfloat",fread($handle,4));
				$vertex3y = unpack("gfloat",fread($handle,4));
				$vertex3z = unpack("gfloat",fread($handle,4));
				$attribBytes = unpack("Sshort",fread($handle,2));
				$this->addTriangle($vertexNx['float'],$vertexNy['float'],$vertexNz['float'],
						$vertex1x['float'],$vertex1y['float'],$vertex1z['float'],
						$vertex2x['float'],$vertex2y['float'],$vertex2z['float'],
						$vertex3x['float'],$vertex3y['float'],$vertex3z['float'],
						$attribBytes); 
			}
		}
	}
	class triangle {
		public $vNormal;
		public $v1;
		public $v2;
		public $v3;
		public $attribBytes;
		private $filterNum;
		function __construct() {
			$this->vNormal = new vertex();
			$this->v1 = new vertex();
			$this->v2 = new vertex();
			$this->v3 = new vertex();
			$this->attribBytes = 0;
			$filterNum = "";
		}
		public function addVertex($nX,$nY,$nZ,
					$oneX,$oneY,$oneZ,
					$twoX,$twoY,$twoZ,
					$threeX,$threeY,$threeZ,
					$attribBytes,$filterNum=0){
			$this->vNormal->a = $nX;
			$this->vNormal->b = $nY;
			$this->vNormal->c = $nZ;
			$this->v1->x = $oneX;
			$this->v1->y = $oneY;
			$this->v1->z = $oneZ;
			$this->v2->x = $twoX;
			$this->v2->y = $twoY;
			$this->v2->z = $twoZ;	
			$this->v3->x = $threeX;
			$this->v3->y = $threeY;
			$this->v3->z = $threeZ;
			$this->attribBytes = $attribBytes;	
			$this->filterNum = $filterNum;		
		}
		public function setFilterNum($filter){
			$this->filterNum = $filter;
		}
		public function getFilterNum(){
			return $this->filterNum;
		}
	}
	class vertex {
		public $x;
		public $y;
		public $z;
	}
?>
