<?php
namespace Jasny\Component\Property;

use Symfony\Component\HttpFoundation\File\File as FsFile;
use Symfony\Component\HttpFoundation\File\UploadFile;

/**
 * Save file with a name referencing the entity, instead of writing the filename to the DB.
 */
class File
{
    protected $entity;
    protected $property;

    protected $dirname;
    protected $format;
    protected $types;

    protected $dir;
    protected $name;
    protected $file;
    

    /**
     * Create an Image object for an entity.
     * 
     * @param object $entity
     * @param string $dirname      relative path
     * @param array  $types        allowed file types
     * @param string $property     which property of the entity to use (should be unique)
     * @param string $format       sprintf format
     */
    public function __construct($entity, $dirname, $types, $property='id', $format=null)
    {
        if (!isset($format)) {
            $format = strtolower(preg_replace('/^.*\W/', '', get_class($entity))) . '-' . ($property == 'id' ? '%08d' : '%s');
        }
        
        $this->entity = $entity; // We're creating a cross-reference here
        $this->property = $property;
        
        $this->dirname = $dirname;
        $this->format = $format;
        $this->types = (array)$types;
    }
    
    
    /**
     * Get the relative path of the directory
     * 
     * @return string
     */
    public function getDirname()
    {
        return $this->dirname;
    }
    
    /**
     * Get the directory
     * 
     * @return SplFileInfo
     */
    public function getDir()
    {
        if (isset($this->dir)) return $this->dir;
        
        global $kernel;
        $this->dir = new \SplFileInfo(dirname($kernel->getRootDir()). '/web/' . $this->getDirname());
        return $this->dir;
    }
    
    
    /**
     * Determine the filename.
     * 
     * @param boolean $new  Use the replacement
     * @return string
     */
    protected function determineName($new=true)
    {
        if (isset($this->name) && (!$new || !isset($this->file))) return $this->name;
        
        $id = $this->entity->{'get' . $this->property}();
        if (!isset($id)) return null;
        
        $name = sprintf($this->format, $id);
        if (count($this->types) == 1) {
            $this->name = $name . '.' . reset($this->types);
            
        } elseif ($new && isset($this->file) && $this->isValid()) {
            $this->name =  $name . '.' . $this->file->guessExtension();
            
        } else {
            $format = preg_replace_callback('/%\w(\d)\w/', function ($match) { return str_repeat('?', $match[1]); }, $this->format);
            $format = preg_replace('/%\w/', '*', $format);

            $files = glob($this->getDir() . '/' . $format . '.{' . join(',', $this->types) . '}', GLOB_BRACE);
            $this->name = !empty($files) ? basename($files[0]) : false;
        }
        
        return $this->name;
    }
    
    /**
     * Get the filename.
     * 
     * Returns null if there more than one extension is possible and the file doesn't exists.
     * If a replacement is set, it will return the name of what the replacement will be.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->determineName() ?: null;
    }
    
    /**
     * Get relative link to image
     * 
     * @param string $prefix
     * @return string 
     */
    public function getLink($prefix=null)
    {
        $file = $this->determineName();
        if (!$file) return null;
        
        return $this->getDirname() . "/" . ($prefix ? "$prefix." : '') . $file;
    }

    /**
     * Get link as asset, ideal for twig.
     * Returns null if the file does not exists (in contrary to getLink)
     * 
     * @param string $prefix
     * @return string
     */
    public function getAsset($prefix=null)
    {
        $file = $this->determineName();
        if (!$file) return null;
        
        $path = $this->getDir() . "/$file";
        if (!file_exists($path)) return null;
        
        global $kernel;
        return $kernel->getContainer()->get('templating.helper.assets')->getUrl($this->getDirname() . '/' . ($prefix ? "$prefix." : '') . $file) . '?v=' . substr(filemtime($path), -5);
    }

    
    /**
     * Return the file
     * 
     * @return FsFile 
     */
    public function getFile()
    {
        $file = $this->determineName();
        return $file ? new FsFile($this->getDir() . "/$file", false) : null;
    }

    /**
     * Returns the file, ignoring the replacement
     * 
     * @return FsFile
     */
    public function getOldFile()
    {
        $file = $this->determineName(false);
        return $file ? new FsFile($this->getDir() . "/$file", false) : null;
    }
    
    /**
     * Check if the file exists
     * 
     * @return boolean
     */
    public function exists()
    {
        $file = $this->determineName();
        return $file && file_exists($this->getDir() . "/$file");
    }
    
    /**
     * Set replacement file.
     * Fluent interface
     * 
     * @param FsFile $upload
     * @return File
     */
    public function replace(FsFile $file)
    {
        $this->file = $file;
        return $this;
    }    
    
    /**
     * Get unpersisted uploaded file
     * 
     * @return FsFile
     */
    public function getReplacement()
    {
        return $this->file;
    }

    /**
     * On persist the image should be deleted.
     * Fluent interface
     * 
     * @return File
     */
    public function clear()
    {
        $this->file = false;
        return $this;
    }

    
    /**
     * Returns the upload error.
     *
     * If the upload was successful, the constant UPLOAD_ERR_OK is returned.
     * Otherwise one of the other UPLOAD_ERR_XXX constants is returned.
     *
     * @return integer
     */
    public function getError()
    {
        if (!isset($this->file)) return null;
        if ($this->file instanceof UploadFile || !$this->file->isValid()) return $this->file->getError();
        if (!in_array($this->file->guessExtension(), $this->types)) return UPLOAD_ERR_EXTENSION;

        return UPLOAD_ERR_OK;
    }
    
    /**
     * Returns whether the file was uploaded successfully.
     * 
     * @return boolean
     */
    public function isValid()
    {
        return $this->getError() == UPLOAD_ERR_OK;
    }
    
    
    /**
     * Move uploaded file from temp dirname to image dirname
     */
    public function persist()
    {
        if (!isset($this->file) || !$this->isValid()) return;
        
        if (!file_exists($this->getDir())) mkdir($this->getDir(), 0777, true);
        
        $old = $this->getOldFile();
        if (isset($old) && file_exists($old)) unlink($old);
        foreach (glob(dirname($old) . '/*.' . basename($old)) as $file) {
            unlink($file);
        }
        
        if ($this->file) $this->file->move($this->getDirname(), $this->determineName(true));
        $this->file = null;
    }
    
    /**
     * Remove the replacement file (don't persist)
     * 
     */
    public function reset()
    {
        $this->file = null;
    }
    
    
    /**
     * Cast to string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getAsset() ?: '';
    }
}
