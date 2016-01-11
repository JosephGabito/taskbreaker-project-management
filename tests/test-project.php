<?php

include __DIR__ . '/../models/project.php';

class ThriveProjectTest extends WP_UnitTestCase {

	public function testSaveShouldReturnFalseIfTitleAndContentAndGroupIdIsEmpty() {

		// Setup.
		$a = new ThriveProject(); 

		// Popular Some Data
		$a->set_title( '' )
		  ->set_content( '' )
		  ->set_group_id( 0 );

		// Assert false.
		$this->assertFalse( $a->save() );

	}

	public function testSaveShouldReturnTrueIfDataIsPopulated() {

		$project = new ThriveProject();

		$project->set_title('Hello TestCase')
		        ->set_content("I'm a new TestCase.")
		        ->set_group_id(1);

		// Assert
		$this->assertTrue( $project->save() );  
		
	}

	public function testDeleteShouldReturnFalseWhenIdIsEmpty() {

		$project = new ThriveProject();

		$project->set_id(0);

		$this->assertFalse( $project->delete() );
	}

	public function testDeleteShouldReturnTrueWhenIdIsProvided() {

		$project = new ThriveProject();
		
		$post_id = $this->factory->post->create(array(
				'post_title' => 'Test C12ase',
				'post_type' => 'project',
			));

		$project->set_id($post_id);

		$this->assertTrue($project->delete(), 'ID');

		$this->assertNotEmpty( $project->get_id() );

	}

}
?>