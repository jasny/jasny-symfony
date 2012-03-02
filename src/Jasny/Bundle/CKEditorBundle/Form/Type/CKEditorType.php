<?php

/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\Bundle\CKEditorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;


class CKEditorType extends AbstractType
{
    /**
     * Relative path to ckeditor assets in web directory
     */
    protected $asset_path = 'bundles/jasnyckeditor/ckeditor';
    
    /**
     * Load on first instance of CKEditor
     */
    static protected $autoload = true;
    
    
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('config', $options['config']);
        $builder->setAttribute('javascript', $options['javascript']);
        $builder->setAttribute('load_ckeditor', $options['load_ckeditor']);
        $builder->setAttribute('kcfinder', $options['kcfinder']);
        
        // Set height of the textbox
        $height = isset($options['config']['height']) ? $options['config']['height'] : 200;
        $attr = $builder->getAttribute('attr');
        if ((is_int($height) || ctype_digit($height)) && (empty($attr['style']) || !preg_match('/(^|;)\s*height\s*:/i', $attr['style']))) {
            $height += 77; // Asuming 1 toolbar
            $attr['style'] = "height: {$height}px; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; " . (!empty($attr['style']) ? $attr['style'] . ' ' : '');
            $builder->setAttribute('attr', $attr);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        global $kernel;
        
        $name = $form->getParent()->getName() . '[' . $form->getName() . ']';
        $config = $form->getAttribute('config');
        $js = "";
        
        if ($form->getAttribute('kcfinder')) $config += $this->getKCFinderConfig();
        
        if ($form->getAttribute('javascript')) {
            if ($form->getAttribute('load_ckeditor')) {
                $src = $kernel->getContainer()->get('templating.helper.assets')->getUrl($this->asset_path . '/ckeditor.js');
                $js .= '<script type="text/javascript" src="' . $src . '"></script>' . "\n";
            }
            
            $json_config = json_encode($config);
            $js .= <<<SCRIPT
<script type="text/javascript">
//<![CDATA[
  CKEDITOR.replace('$name', $json_config)
//]]>
</script>
SCRIPT;
        }
        
        $view->set('config', $config);
        $view->set('javascript', $js);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $load = self::$autoload;
        self::$autoload = false;
        
        return array(
            'config' => array(),
            'javascript' => true,
            'load_ckeditor' => $load,
            'kcfinder' => true,
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'textarea';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ckeditor';
    }
    
    /**
     * Get KCFinder configuration
     * 
     * @return array
     */
    protected function getKCFinderConfig()
    {
        global $kernel;
        $kcfinder = $kernel->getContainer()->get('ckeditor.kcfinder');
        
        $kcfinder->enable();
        return $kcfinder->getCKEditorConfig();
    }
}