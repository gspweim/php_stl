<?php
/**
 * Stl File Doc Comment
 *
 * PHP Version 5.3
 * 
 * @category Stl
 * @package  None
 * @author   GspWeim <gspweim@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/gspweim/php_stl
 */
error_reporting(E_ALL | E_STRICT);
if (isset($argv[1])) {
    $fileName=$argv[1];
    $inFile = new StlFile();
    $inFile->loadFile($fileName);
    echo "Total Triangles Start " . $inFile->getNumTriangles() ."\n";
    //now lets cut
    $trianglesLeft = $inFile->filterZ(40, ">");
    echo "Total Triangles Left after Z cut " . $inFile->getNumTriangles() ."\n";
    $trianglesLeft = $inFile->filterY(10, ">");
    echo "Total Triangles Left after Y cut " . $inFile->getNumTriangles() ."\n";
    $inFile->writeFile("out.stl");
    

    echo "Header " . $inFile->getHeader()  ."\n";
    echo "Total Triangles " . $inFile->getNumTriangles() ."\n";
    echo "Max/Min " .  $inFile->getMaxX() . "," . $inFile->getMaxY() . 
        "," . $inFile->getMaxZ() . " : " .  $inFile->getMinX() . "," . 
        $inFile->getMinY() . "," . $inFile->getMinZ() . "\n";
} else {
    echo "Please pass in a stl filename \n";
}

exit();
    
/**
 * StlFile Class Doc Comment
 *
 * @category Stl
 * @package  None
 * @author   GspWeim <gspweim@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/gspweim/php_stl
 */
     
class StlFile
{
    private $_numTriangles;
    private $_triangles;
    private $_header;
    private $_maxX;
    private $_minX;
    private $_maxY;
    private $_minY;
    private $_maxZ;
    private $_minZ;
    private $_fileName;
    private $_filterOn;
    
    /**
     * Constructer, initializes variables.
     *
     * @return null
     */
    function __construct() 
    {
        $this->_header = "";
        $this->_numTriangles = 0;
        $this->_triangles =  array();
        $this->_maxX = 0;
        $this->_minX = 99999;
        $this->_maxY = 0;
        $this->_minY = 99999;
        $this->_maxZ = 0;
        $this->_minZ = 99999;
        $this->_fileName = "";
        $this->_filterOn = 0;
    }

    /**
     * Returns number of triangles in this stl.
     *
     * @return int 
     */
    public function getNumTriangles() 
    {
        //no setter since adding triangle counts it
        //lets just count the ones for the current function
        $triangleCount = 0;
        foreach ($this->_triangles as $triangle) {
            if ($triangle->getFilterNum() == $this->_filterOn) {
                $triangleCount++;
            }
        }
        return $triangleCount;
    }
    /**
     * Setter, sets the header for the stl file.
     *
     * @param string $header the header for the stl file
     * 
     * @return null
     */
    public function setHeader($header) 
    {
        $this->_header = $header;
    }
    /**
     * Getter, gets the header for the stl file.
     *
     * @return string
     */
    public function getHeader() 
    {
        return $this->_header;
    }
    /**
     * Add a single Triangle to stl
     *
     * @param int $nX          the magnitude of the vector for X 
     * @param int $nY          the magnitude of the vector for Y
     * @param int $nZ          the magnitude of the vector for Z
     * @param int $oneX        the first vertex X
     * @param int $oneY        the first vertex X
     * @param int $oneZ        the first vertex X
     * @param int $twoX        the second vertex X
     * @param int $twoY        the second vertex Y
     * @param int $twoZ        the second vertex Z
     * @param int $threeX      the third vertex X
     * @param int $threeY      the third vertex Y
     * @param int $threeZ      the third vertex Z 
     * @param int $attribBytes the attribute bytes param
     * 
     * @return null
     */
    public function addTriangle(
        $nX,$nY,$nZ,
        $oneX,$oneY,$oneZ,
        $twoX,$twoY,$twoZ,
        $threeX,$threeY,$threeZ,
        $attribBytes
    ) {
        $triangle = new Triangle();
        $triangle->addVertex(
            $nX, $nY, $nZ,
            $oneX, $oneY, $oneZ,
            $twoX, $twoY, $twoZ,
            $threeX, $threeY, $threeZ,
            $attribBytes, $this->_filterOn
        );
        $this->_numTriangles++;
        $this->_triangles[] = $triangle;
        $this->_calcMinMax($oneX, $oneY, $oneZ);
        $this->_calcMinMax($twoX, $twoY, $twoZ);
        $this->_calcMinMax($threeX, $threeY, $threeZ);

    }
    /**
     * Add an array of triangles for the stl file.
     *
     * @param array $triangles an array of triangle to add
     * 
     * @return null
     */    
    public function addTriangles($triangles)
    {
        //going to split them out for fun and to use the logic
        foreach ($triangles as $triangle) {
            $this->addTriangle(
                $triangle->vNormal->x, $triangle->vNormal->y,
                $triangle->vNormal->z,
                $triangle->v1->x, $triangle->v1->y, $triangle->v1->z,
                $triangle->v2->x, $triangle->v2->y, $triangle->v2->z,
                $triangle->v3->x, $triangle->v3->y, $triangle->v3->z,
                $triangle->attribBytes
            );
        }

    }
    /**
     * Private function to calc the min and max variables for loaded
     * triangles. Pass in the x,y,z for each triangle added I think
     *
     * @param int $x the x value to check
     * @param int $y the y value to check
     * @param int $z the z value to check
     * 
     * @return null
     */ 
    private function _calcMinMax($x,$y,$z)
    {
        if ($x>$this->_maxX) {
            $this->_maxX = $x;
        }
        if ($x<$this->_minX) {
            $this->_minX = $x;
        }
        if ($y>$this->_maxY) {
            $this->_maxY = $y;
        }
        if ($y<$this->_minY) {
            $this->_minY = $y;
        }
        if ($z>$this->_maxZ) {
            $this->_maxZ = $z;
        }
        if ($z<$this->_minZ) {
            $this->_minZ = $z;
        }
    }
    /**
     * Getter, get the min X
     *
     * @return int
     */
    public function getMinX()
    {
        return $this->_minX;
    }
    /**
     * Getter, get the max X
     *
     * @return int
     */
    public function getMaxX()
    {
        return $this->_maxX;
    }
    /**
     * Getter, get the min Y
     *
     * @return int
     */
    public function getMinY()
    {
        return $this->_minY;
    }
    /**
     * Getter, get the max Y
     *
     * @return int
     */
    public function getMaxY()
    {
        return $this->_maxY;
    }
    /**
     * Getter, get the min Z
     *
     * @return int
     */
    public function getMinZ()
    {
        return $this->_minZ;
    }
    /**
     * Getter, get the max Z
     *
     * @return int
     */
    public function getMaxZ()
    {
        return $this->_maxZ;
    }
    /**
     * Add a filter for the Z vertex
     *
     * @param int    $z        value for Z you want to filter on
     * @param string $operator the operator or test you want to do
     *                         against z "<" is currently only choice
     * 
     * @return null
     */
    public function filterZ($z,$operator ="<")
    {
        $lastFilter = $this->_filterOn;
        $this->_filterOn++;      
        $trianglesAdded = 0;    
        foreach ($this->_triangles as $triangle) {
            if ($triangle->getFilterNum() == $lastFilter) {
                if ($operator == "<") {
                    if ((($triangle->v1->z - $this->_minZ) < $z) 
                        && (($triangle->v2->z - $this->_minZ) < $z) 
                        && (($triangle->v3->z - $this->_minZ) < $z) 
                    ) {
                        $triangle->setFilterNum($this->filterOn);
                        $trianglesAdded++;
                    }
                } else {
                    if ((($triangle->v1->z - $this->_minZ) >= $z) 
                        && (($triangle->v2->z - $this->_minZ) >= $z) 
                        &&(($triangle->v3->z - $this->_minZ) >= $z)
                    ) {
                        $triangle->setFilterNum($this->_filterOn);
                        $trianglesAdded++;
                    }
                }
            }
        }
        return $trianglesAdded;
    }
    /**
     * Add a filter for the X vertex
     *
     * @param int    $x        value for x you want to filter on
     * @param string $operator the operator or test you want to do
     *                         against x "<" is currently only choice
     * 
     * @return null
     */
    public function filterX($x,$operator ="<")
    {
        $lastFilter = $this->_filterOn;
        $this->_filterOn++;      
        $trianglesAdded = 0;    
        foreach ($this->_triangles as $triangle) {
            if ($triangle->getFilterNum() == $lastFilter) {
                if ($operator == "<") {
                    if ((($triangle->v1->x - $this->_minX) < $x) 
                        && (($triangle->v2->x - $this->_minX) < $x) 
                        && (($triangle->v3->x - $this->_minX) < $x) 
                    ) {
                        $triangle->setFilterNum($this->_filterOn);
                        $trianglesAdded++;
                    }
                } else {
                    if ((($triangle->v1->x - $this->_minX) >= $x) 
                        && (($triangle->v2->x - $this->_minX) >= $x) 
                        && (($triangle->v3->x - $this->_minX) >= $x) 
                    ) {
                        $triangle->setFilterNum($this->_filterOn);
                        $trianglesAdded++;
                    }
                }
            }
        }
        return $trianglesAdded;
    }
    /**
     * Add a filter for the Y vertex
     *
     * @param int    $y        value for Y you want to filter on
     * @param string $operator the operator or test you want to do
     *                         against y "<" is currently only choice
     * 
     * @return null
     */
    public function filterY($y,$operator ="<")
    {
        $lastFilter = $this->_filterOn;
        $this->_filterOn++;      
        $trianglesAdded = 0;    
        foreach ($this->_triangles as $triangle) {
            if ($triangle->getFilterNum() == $lastFilter) {
                if ($operator == "<") {
                    if ((($triangle->v1->y - $this->_minY) < $y) 
                        && (($triangle->v2->y - $this->_minY) < $y) 
                        && (($triangle->v3->y - $this->_minY) < $y) 
                    ) {
                        $triangle->setFilterNum($this->_filterOn);
                        $trianglesAdded++;
                    }
                } else {
                    if ((($triangle->v1->y - $this->_minY) >= $y) 
                        && (($triangle->v2->y - $this->_minY) >= $y) 
                        && (($triangle->v3->y - $this->_minY) >= $y) 
                    ) {
                        $triangle->setFilterNum($this->_filterOn);
                        $trianglesAdded++;
                    }
                }
            }
        }
        return $trianglesAdded;
    }
    /**
     * Write the triangles to an STLfile
     *
     * @param string $fileName The name of the file to write to
     * 
     * @return null
     */   
    public function writeFile($fileName)
    {
        $handle = fopen($fileName, "wb");
        fwrite($handle, $this->_header, 80);
        fwrite($handle, pack("l", $this->getNumTriangles()), 4);
        foreach ($this->_triangles as $triangle) {
            if ($triangle->getFilterNum() == $this->_filterOn) {
                fwrite($handle, pack("g", $triangle->vNormal->x), 4);
                fwrite($handle, pack("g", $triangle->vNormal->y), 4);
                fwrite($handle, pack("g", $triangle->vNormal->z), 4);
                fwrite($handle, pack("g", $triangle->v1->x), 4);
                fwrite($handle, pack("g", $triangle->v1->y), 4);
                fwrite($handle, pack("g", $triangle->v1->z), 4);
                fwrite($handle, pack("g", $triangle->v2->x), 4);
                fwrite($handle, pack("g", $triangle->v2->y), 4);
                fwrite($handle, pack("g", $triangle->v2->z), 4);
                fwrite($handle, pack("g", $triangle->v3->x), 4);
                fwrite($handle, pack("g", $triangle->v3->y), 4);
                fwrite($handle, pack("g", $triangle->v3->z), 4);
                fwrite($handle, pack("S", $triangle->attribBytes), 2);
            }
        }
        fclose($handle);
    }
    /**
     * Load the triangles from an STLfile
     *
     * @param string $fileName The name of the file to read from
     * 
     * @return null
     */ 
    public function loadFile($fileName)
    {
        $this->_fileName = $fileName;
        $handle = fopen($fileName, "rb");
        $this->_readHeader($handle);
        $numTriangles = $this->_readNumTriangles($handle);
        $this->_readTriangles($handle, $numTriangles);
        fclose($handle);    
    }
    /**
     * Read the header from the opened fie
     *
     * @param int $handle The filehandle of the opened file
     * 
     * @return null
     */ 
    private function _readHeader($handle)
    {
        $this->_header = fread($handle, 80);
    }
    /**
     * Read the number of triangles from the opened fie
     *
     * @param int $handle The filehandle of the opened file
     * 
     * @return null
     */ 
    private function _readNumTriangles($handle) 
    {
        $numTriangles = fread($handle, 4);
        $uITriangles = unpack("luint", $numTriangles);           
        //dont set class triangles here in case we want to combine stl(s)
        //$this->numTriangles = $uITriangles['uint'];
        return $uITriangles['uint'];
    }
    /**
     * Read the triangles from the opened fie
     *
     * @param int $handle       The filehandle of the opened file
     * @param int $numTriangles The number of triangles to read
     * 
     * @return null
     */ 
    private function _readTriangles($handle,$numTriangles)
    {
        for ($x=0;$x<$numTriangles;$x++) {
            $vertexNx = unpack("gfloat", fread($handle, 4));
            $vertexNy = unpack("gfloat", fread($handle, 4));
            $vertexNz = unpack("gfloat", fread($handle, 4));
            $vertex1x = unpack("gfloat", fread($handle, 4));
            $vertex1y = unpack("gfloat", fread($handle, 4));
            $vertex1z = unpack("gfloat", fread($handle, 4));
            $vertex2x = unpack("gfloat", fread($handle, 4));
            $vertex2y = unpack("gfloat", fread($handle, 4));
            $vertex2z = unpack("gfloat", fread($handle, 4));
            $vertex3x = unpack("gfloat", fread($handle, 4));
            $vertex3y = unpack("gfloat", fread($handle, 4));
            $vertex3z = unpack("gfloat", fread($handle, 4));
            $attribBytes = unpack("Sshort", fread($handle, 2));
            $this->addTriangle(
                $vertexNx['float'], $vertexNy['float'], $vertexNz['float'],
                $vertex1x['float'], $vertex1y['float'], $vertex1z['float'],
                $vertex2x['float'], $vertex2y['float'], $vertex2z['float'],
                $vertex3x['float'], $vertex3y['float'], $vertex3z['float'],
                $attribBytes
            ); 
        }
    }
}
/**
 * Triangle Class Doc Comment
 *
 * @category Stl
 * @package  None
 * @author   GspWeim <gspweim@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/gspweim/php_stl
 */
class Triangle
{
    public $vNormal;
    public $v1;
    public $v2;
    public $v3;
    public $attribBytes;
    private $_filterNum;
    /**
     * Constructer, initializes variables.
     *
     * @return null
     */
    function __construct()
    {
        $this->vNormal = new Vertex();
        $this->v1 = new Vertex();
        $this->v2 = new Vertex();
        $this->v3 = new Vertex();
        $this->attribBytes = 0;
        $this->_filterNum = "";
    }
    /**
     * Add a vertex
     *
     * @param int $nX          the magnitude of the vector for X 
     * @param int $nY          the magnitude of the vector for Y
     * @param int $nZ          the magnitude of the vector for Z
     * @param int $oneX        the first vertex X
     * @param int $oneY        the first vertex X
     * @param int $oneZ        the first vertex X
     * @param int $twoX        the second vertex X
     * @param int $twoY        the second vertex Y
     * @param int $twoZ        the second vertex Z
     * @param int $threeX      the third vertex X
     * @param int $threeY      the third vertex Y
     * @param int $threeZ      the third vertex Z 
     * @param int $attribBytes the attribute bytes param
     * @param int $filterNum   the filter that added this I think
     * 
     * @return null
     */
    public function addVertex(
        $nX,$nY,$nZ,
        $oneX,$oneY,$oneZ,
        $twoX,$twoY,$twoZ,
        $threeX,$threeY,$threeZ,
        $attribBytes,$filterNum=0
    ) {
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
        $this->_filterNum = $filterNum;      
    }
    /**
     * Setter, sets the filter number for this vertex.
     *
     * @param int $filter the filer num for this vertex
     * 
     * @return null
     */
    public function setFilterNum($filter)
    {
        $this->_filterNum = $filter;
    }
    /**
     * Getter, gets the filter number for this vertex.
     *
     * @return int
     */
    public function getFilterNum()
    {
        return $this->_filterNum;
    }
}
/**
 * Vertex Class Doc Comment
 *
 * @category Stl
 * @package  None
 * @author   GspWeim <gspweim@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://github.com/gspweim/php_stl
 */
class Vertex
{
    public $x;
    public $y;
    public $z;
}
?>
