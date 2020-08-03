<?

namespace App\Tests\EventSubscriber;

use PHPUnit\Framework\TestCase;
use App\EventSubscriber\ExceptionSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class EventSubscriberTest extends TestCase {
    
    public function testEventSubscription()
    {
        $this->assertArrayHasKey(ExceptionEvent::class,  ExceptionSubscriber::getSubscribedEvents());
    }

    public function testOnExceptionSendEmail()
    {
        $mailer = $this->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mailer->expects($this->once())->method('send');
        $this->dispatch($mailer);
        
    }

    public function testOnExceptionSendEmailToTheAdmin()
    {
        $mailer = $this->getMockBuilder(\Swift_Mailer::class)
        ->disableOriginalConstructor()
        ->getMock();

        $mailer->expects($this->once())
            ->method('send')
            ->with($this-> callback(function(\Swift_Message $message){
                return
                    array_key_exists('from@domain.fr', $message->getFrom()) &&
                    array_key_exists('to@domain.fr', $message->getTo());
            }));
        $this->dispatch($mailer);
    }

    public function testOnExceptionSendEmailWithTheTrace()
    {
        $mailer = $this->getMockBuilder(\Swift_Mailer::class)
        ->disableOriginalConstructor()
        ->getMock();

        $mailer->expects($this->once())
            ->method('send')
            ->with($this-> callback(function(\Swift_Message $message){
                return
                    strpos($message->getBody(), 'EventSubscriberTest') &&
                    strpos($message->getBody(), 'Hello world')
                    ;
            }));
        $this->dispatch($mailer);
    }

    private function dispatch($mailer)
    {
        
        $subscriber = new ExceptionSubscriber($mailer, 'from@domain.fr', 'to@domain.fr');
        $kernel = $this->getMockBuilder(KernelInterface::class)->getMock();
        $event = new ExceptionEvent($kernel, new Request(), 1, new \Exception("Hello world"));
        $mailer->expects($this->once())->method('send');
        //$subscriber->onException($event);

        //instead of using a subscriber, use the dispatcher
        //this allows to make sure the name of the subscriber is correct in getSubscribedEvents()
        //no need for testEventSubscription() in this case
        //however it could be great to keep both
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($subscriber);
        $dispatcher->dispatch($event);
    }
}