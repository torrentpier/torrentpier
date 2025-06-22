<?php

declare(strict_types=1);

namespace Tests\Unit\Events;

use App\Events\UserRegistered;
use App\Events\TorrentUploaded;
use Tests\TestCase;

class EventSystemTest extends TestCase
{
    /**
     * Test that events can be dispatched
     */
    public function testCanDispatchEvents(): void
    {
        // Create a mock listener
        $called = false;
        $eventData = null;
        
        app('events')->listen(UserRegistered::class, function ($event) use (&$called, &$eventData) {
            $called = true;
            $eventData = $event;
        });
        
        // Dispatch the event
        $event = new UserRegistered(
            userId: 123,
            username: 'testuser',
            email: 'test@example.com',
            registeredAt: new \DateTime('2025-01-01 12:00:00')
        );
        
        event($event);
        
        // Assert the listener was called
        $this->assertTrue($called);
        $this->assertInstanceOf(UserRegistered::class, $eventData);
        $this->assertEquals(123, $eventData->getUserId());
        $this->assertEquals('testuser', $eventData->getUsername());
        $this->assertEquals('test@example.com', $eventData->getEmail());
    }
    
    /**
     * Test event helper function
     */
    public function testEventHelperFunction(): void
    {
        $listenerCalled = false;
        
        app('events')->listen(TorrentUploaded::class, function () use (&$listenerCalled) {
            $listenerCalled = true;
        });
        
        // Use the event() helper
        event(new TorrentUploaded(
            torrentId: 456,
            uploaderId: 789,
            torrentName: 'Test Torrent',
            size: 1024 * 1024 * 100, // 100MB
            uploadedAt: new \DateTime()
        ));
        
        $this->assertTrue($listenerCalled);
    }
    
    /**
     * Test that multiple listeners can be attached to an event
     */
    public function testMultipleListeners(): void
    {
        $listener1Called = false;
        $listener2Called = false;
        
        app('events')->listen(UserRegistered::class, function () use (&$listener1Called) {
            $listener1Called = true;
        });
        
        app('events')->listen(UserRegistered::class, function () use (&$listener2Called) {
            $listener2Called = true;
        });
        
        event(new UserRegistered(
            userId: 999,
            username: 'multitest',
            email: 'multi@test.com',
            registeredAt: new \DateTime()
        ));
        
        $this->assertTrue($listener1Called);
        $this->assertTrue($listener2Called);
    }
}