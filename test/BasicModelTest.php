<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once 'test/TestHelper.php';

final class BasicModelTest extends TestCase
{
  public function testCreate(): void
  {
    $this->assertCount(0, Post::all());

    $post = new Post;
    $post->set_title('hello codehit!');
    $post->set_description('welcome home!');
    $post->save();

    $this->assertCount(1, Post::all());
  }

  /**
   * @depends testCreate
   */
  public function testGetters(): void
  {
    $post = Post::find(1);
    $this->assertEquals('hello codehit!', $post->get_title());
    $this->assertEquals('welcome home!', $post->get_description());
  }

  /**
   * @depends testCreate
   */
  public function testUpdate(): void
  {
    $post = Post::find(1);

    $post->set_title('holla codehit!');
    $post->set_description('welcome hombre!');
    $post->save();

    $this->assertEquals('holla codehit!', $post->get_title());
    $this->assertEquals('welcome hombre!', $post->get_description());

    $this->assertCount(1, Post::all());
  }

  /**
   * @depends testCreate
   */
  public function testDestroy(): void
  {
    $post = Post::find(1);
    $post->destroy();

    $this->assertCount(0, Post::all());
  }
}