<?php namespace Friendica\Directory\Rendering;

use \Closure;

/**
 * This class with insert data in a two-step view.
 */
class View
{
    
    #TODO: Replace this with better code.
    
    public static function getViewPath($name)
    {
        return dirname(__DIR__).'/templates/view/'.$name.'.php';
    }
    
    public static function getLayoutPath($name)
    {
        return dirname(__DIR__).'/templates/layout/'.$name.'.php';
    }
    
    protected $layout;
    protected $view;
    protected $helpers;
    
    public function getHelpers(){
      return $this->helpers;
    }
    
    public function addHelper($name, Closure $helper)
    {
        $this->helpers[$name] = $helper;
    }
    
    public function getView(){
        return $this->view;
    }

    public function setView($value){
        $this->view = $value;
    }

    public function getLayout(){
        return $this->layout;
    }

    public function setLayout($value){
        $this->layout = $value;
    }

    public function __construct($view=null, $layout="default")
    {
        
        $this->view = $view;
        $this->layout = $layout;
        $this->helpers = array();
        
    }

    public function render(array $data=array())
    {
        
        //First the outer view.
        $view = self::getViewPath($this->view);
        $viewContent = $this->encapsulatedRequire($view, $data);
        
        //Then the layout, including the view as $content.
        $data['content'] = $viewContent;
        $layout = self::getLayoutPath($this->layout);
        return $this->encapsulatedRequire($layout, $data);
        
    }
    
    public function output(array $data=array())
    {
        
        header("Content-type: text/html; charset=utf-8");
        echo $this->render($data);
        exit;
        
    }
    
    public function encapsulatedRequire($filename, array $data=null)
    {
        
        //This will provide our variables on the global scope.
        $call = function($__FILE__, $__VARS__){
            extract($__VARS__, EXTR_SKIP);
            require $__FILE__;
        };
        
        //Use our current data as fallback.
        if(!is_array($data)){
            $data = $this->currentData;
        }
        
        //This will add the helper class to $this.
        $helpers = new ViewHelpers($this, $data);
        $call = $call->bindTo($helpers, get_class($helpers));
        
        //Run and return the value.
        ob_start();
        $call($filename, $data);
        return ob_get_clean();
        
    }
    
}