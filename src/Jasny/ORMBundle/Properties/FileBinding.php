<?php
/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle\Properties;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadFile;

/**
 * Save file with a name referencing the entity, instead of writing the filename to the DB.
 */
class FileBinding implements Persistable
{
    protected $entity;
    protected $property;
    protected $id;

    protected $dirname;
    protected $format;
    protected $types;

    /**
     * @var \SplFileInfo
     */
    protected $path;
    protected $name;

    /**
     * @var File
     */
    protected $replacement;
    
    public $prefixSupported = true;

    /**
     * Create an File object for an entity.
     * 
     * @param object       $entity
     * @param string       $dirname   relative path
     * @param string|array $types     allowed filetype extension(s)
     * @param string       $format    sprintf format for filename
     * @param string       $property  which property of the entity to use (should be a unique reference, only consisting of wordchars and '-')
     */
    public function __construct($entity, $dirname, $types, $format=null, $property='id')
    {
        if (!isset($format)) {
            $format = $property == 'id' ? strtolower(preg_replace('~([a-z])([A-Z])|\\\\~', '$1_$2', $entity)) . '-' . '%08d' : '%s';
        }
        
        $this->entity = $entity; // We're creating a cross-reference here
        $this->property = $property;
        $this->id = $this->entity->{'get' . str_replace('_', '', $this->property)}();
        
        $this->dirname = $dirname;
        $this->format = $format;
        $this->types = (array)$types;
        
        $this->name = $this->determineName();
    }
    
    /**
     * Determine the filename.
     * 
     * @param boolean $new  Use the extension of the replacement.
     * @return string
     */
    protected function determineName($new=true)
    {
        if (!isset($this->id)) return null;
        
        $id = preg_replace('/[^\w\-]+/', '-', $this->id); // Can cause problems when using a crappy property, but is at least save.
        $name = sprintf($this->format, $id);
        
        if (count($this->types) == 1) {
            $name .= '.' . reset($this->types);
        } elseif ($new && $this->replacement && $this->isValid()) {
            $name .= '.' . $this->replacement->guessExtension();
        } else {
            $files = glob($this->getPath() . '/' . $name . '.{' . join(',', $this->types) . '}', GLOB_BRACE);
            $name = !empty($files) ? substr($files[0], strlen($this->getPath()) + 1) : null;
        }
        
        return $name;
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
        return $this->name;
    }
    
    /**
     * Get the relative path of the directory.
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
    public function getPath()
    {
        if (!isset($this->path)) {
            global $kernel;
            $dir = $kernel->getContainer()->get('templating.helper.assets')->getUrl($this->getDirname());
            $this->path = new \SplFileInfo(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $dir);
        }
        
        return $this->path;
    }
    
    /**
     * Return the file.
     * 
     * If the file is being replaced, return the replacement file.
     * 
     * @return File
     */
    public function getFile()
    {
        if (isset($this->replacement)) return $this->replacement ?: null;
        
        $name = $this->getName();
        return $name ? new File($this->getPath() . '/' . $name, false) : null;
    }
    
    /**
     * Return the name of the file.
     * 
     * If the file is being replaced, return the replacement filename.
     * 
     * @return string
     */
    public function getFilename()
    {
        if (isset($this->replacement)) return $this->replacement->getBasename() ?: null;
        return $this->getName();
    }
    
    /**
     * Check if the file exists.
     * 
     * @return boolean
     */
    public function exists()
    {
        $file = $this->getFile();
        if (empty($file)) return false;
        
        return file_exists($file->getPathname());
    }
    
    /**
     * Get relative link to file.
     * 
     * @param string $prefix
     * @return string 
     */
    public function getLink($prefix=null)
    {
        $name = $this->getName();
        if (!isset($name)) return null;
        
        return $this->getDirname() . "/" . (isset($prefix) && $this->prefixSupported ? preg_replace('~(^.*/)~', '${1}' . $prefix . '.', $name) : $name);
    }

    /**
     * Get link as asset, ideal for twig.
     * Returns a link to the replacement if set and returns null if the file does not exists
     * 
     * @param string $prefix
     * @return string
     */
    public function getAsset($prefix=null, $version=true)
    {
        if ($this->replacement) {
            if (substr($this->replacement->getPathname(), 0, strlen($_SERVER['DOCUMENT_ROOT'])) != $_SERVER['DOCUMENT_ROOT']) return null;
            return substr($this->replacement->getPathname(), strlen($_SERVER['DOCUMENT_ROOT']) - 1) . ($version ? '?v=' . substr(filemtime($this->getFile()), -5) : '');
        }
        
        if (!$this->exists()) return null;
        
        global $kernel;
        return $kernel->getContainer()->get('templating.helper.assets')->getUrl($this->getLink($prefix)) . ($version ? '?v=' . substr(filemtime($this->getFile()), -5) : '');
    }
    
    
    /**
     * Set replacement file.
     * Fluent interface
     * 
     * The replacement file is moved to the web dir with a temporary file name.
     * 
     * @param File $file
     * @return File
     */
    public function replace($file)
    {
        if (!$file instanceof File) return $this->setReplacement($file);
        
        if (substr($file->getPath(), 0, strlen($_SERVER['DOCUMENT_ROOT'])) == $_SERVER['DOCUMENT_ROOT']) {
            $this->replacement = $file;
        } else {
            $this->replacement = $file->move($this->getPath(),  'tmp.' . md5(microtime()) . '.' . $file->guessExtension());
        }
        
        return $this;
    }
    
    /**
     * Get unpersisted uploaded file
     * 
     * @return File
     */
    public function getReplacement()
    {
        return $this->replacement;
    }

    /**
     * Set unpersisted uploaded file.
     * Fluent interface
     * 
     * @param string $file  Filename
     * @return File
     */
    public function setReplacement($filename)
    {
        if ($filename == $this->name) return;
        
        if (empty($filename)) {
            // Delete
            $this->replacement = 0;
        } else {
            // Replace
            if (strpos('..', $filename) !== false || !preg_match('/^tmp\.\d{32}\./', $filename)) throw new \Exception("Illegal replacement filename '$filename'");
            $this->replacement = new File($this->getPath() . '/' . $filename);
        }
        
        $this->name = null;
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
        if (empty($this->replacement)) return null;
        
        $replacement = $this->getReplacement();
        if ($replacement instanceof UploadFile && !$replacement->isValid()) return $replacement->getError();

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
     * Move uploaded file from temp dirname to image dirname.
     * Automatically called by PropertyPersister on entity postPersist and postUpdate event.
     */
    public function persist()
    {
        $id = $this->entity->{'get' . $this->property}(); // The id might have been set/modified on persist
        
        // If the reference has changed make sure to rename / save the file to the proper filename
        if (isset($this->id) && $id != $this->id && !isset($this->replacement)) {
            $this->replacement = $this->getFile();
        }
        
        // Check if there's anything to do
        if (!isset($this->replacement)) return;
        if (!$this->isValid()) throw new \Exception("Unable to persit uploaded file. Upload failed (" . $this->getError() . ")");
        
        // Delete old file
        $this->persistRemove();
        
        // Make sure we use the new id
        if ($this->id != $id) $this->id = $id;
        $this->name = $this->determineName();
        
        // Move or copy replacement file
        if ($this->replacement) {
            $file = new File($this->getPath() . '/' . $this->getName(), false);
            
            if (preg_match('/^tmp\.\d{32}\./', $this->replacement->getBasename())) {
                $this->replacement->move($file->getPath(), $file->getBasename());
            } else {
                if (!file_exists($file->getPath())) mkdir($file->getPath(), 0775, true);
                copy($this->replacement->getPathname(), $file->getPathname());
            }
            
            $this->replacement = null;
        }
    }
    
    /**
     * Delete file.
     * Automatically called by PropertyPersister on entity postRemove event.
     */
    public function persistRemove()
    {
        $old = $this->determineName(false);
        if (!isset($old)) return;
        
        $old = new File($this->getPath() . '/' .$old);
        if (file_exists($old)) {
            if ((string)$old != (string)$this->replacement) unlink($old);
            foreach (glob($old->getPath() . '/*.' . $old->getBasename()) as $file) unlink($file);
        }
    }
    
    
    /**
     * Cast to string, returning the asset.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getAsset() ?: '';
    }
}
