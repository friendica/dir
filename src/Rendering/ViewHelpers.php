<?php namespace Friendica\Directory\Rendering;

use \BadMethodCallException;

/**
 * This provides functions in a view to do things like including new views.
 */
class ViewHelpers
{
    
    protected $view;
    protected $contextData;
    
    public function __construct(View $view, array $contextData)
    {
        $this->view = $view;
        $this->contextData = $contextData;
    }
    
    public function view($name, array $overrides=null)
    {
        
        $data = $this->contextData;
        
        if(is_array($overrides)){
            $data = array_merge($data, $overrides);
        }
        
        return $this->view->encapsulatedRequire(View::getViewPath($name), $data);
        
    }
    
    public function layout($name, array $overrides=null)
    {
        
        $data = $this->contextData;
        
        if(is_array($overrides)){
            $data = array_merge($data, $overrides);
        }
        
        return $this->view->encapsulatedRequire(View::getLayoutPath($name), $data);
        
    }
    
    public function __call($name, $arguments)
    {
        
        $helpers = $this->view->getHelpers();
        
        if(array_key_exists($name, $helpers)){
            return call_user_func_array($helpers[$name], $arguments);
        }
        
        throw new BadMethodCallException("Helper method '$name' does not exist or is not added.");
        
    }
    
}