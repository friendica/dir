<?php namespace Friendica\Directory\Example;

class HelloTest extends \PHPUnit_Framework_TestCase
{
	
	/**
	 * Test the sayHello() method.
	 */
	public function testSayHello()
	{
		
		//Create a new Hello class instance.
		$instance = new Hello();
		
		//Call the method sayHello() that we want to test.
		$output = $instance->sayHello();
		
		//Check that it returns the message we expect.
		$this->assertEquals("Hello world!", $output);
		
	}
	
}