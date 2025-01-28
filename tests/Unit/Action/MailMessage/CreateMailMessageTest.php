<?php

namespace FluxErp\Tests\Unit\Action\MailMessage;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\MailMessage\CreateMailMessage;
use FluxErp\Listeners\MailMessage\CreateMailExecutedSubscriber;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Unit\BaseSetup;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class CreateMailMessageTest extends BaseSetup
{
    private Address $address;

    private MailAccount $mailAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->address = Contact::factory()
            ->has(Address::factory()->for($this->dbClient))
            ->for(PriceList::factory())
            ->for(PaymentType::factory()->hasAttached($this->dbClient))
            ->for($this->dbClient)
            ->create()
            ->addresses()
            ->first();

        $this->mailAccount = MailAccount::factory()
            ->has(MailFolder::factory()->state(['can_create_ticket' => true]))
            ->create();
    }

    public function test_creates_ticket()
    {
        $dispatcher = Event::fake('action.executed: ' . CreateMailMessage::class);
        FluxAction::setEventDispatcher($dispatcher);

        $action = CreateMailMessage::make([
            'mail_account_id' => $this->mailAccount->id,
            'mail_folder_id' => $this->mailAccount->mailFolders->first()->id,
            'from' => 'Tester McTestFace <' . $this->address->email . '>',
            'to' => [$this->mailAccount->email],
            'subject' => $subject = Str::uuid()->toString(),
            'text_body' => faker()->text(),
            'html_body' => '<p>' . faker()->text() . '</p>',
            'communication_type_enum' => 'mail',
            'date' => now()->format('Y-m-d H:i:s'),
            'tags' => [],
        ]);
        $result = $action->validate()->execute();

        $this->assertNotNull($result);
        $this->assertNotNull($result->id);
        Event::assertDispatched('action.executed: ' . CreateMailMessage::class);

        $listener = new CreateMailExecutedSubscriber();
        $listener->handle($action);

        $this->assertDatabaseHas('tickets', [
            'title' => $subject,
            'description' => $action->getData('text_body'),
            'created_by' => $this->address->getMorphClass() . ':' . $this->address->getKey(),
        ]);

        $this->assertTrue($result->communications()->where('communications.id', $result->id)->exists());
    }

    public function test_creates_ticket_comment()
    {
        $dispatcher = Event::fake('action.executed: ' . CreateMailMessage::class);
        FluxAction::setEventDispatcher($dispatcher);
        $ticket = Ticket::factory()->create([
            'authenticatable_type' => $this->address->getMorphClass(),
            'authenticatable_id' => $this->address->getKey(),
        ]);

        $action = CreateMailMessage::make([
            'mail_account_id' => $this->mailAccount->id,
            'mail_folder_id' => $this->mailAccount->mailFolders->first()->id,
            'from' => 'Tester McTestFace <' . $this->address->email . '>',
            'to' => [$this->mailAccount->email],
            'subject' => $subject = Str::uuid()->toString(),
            'text_body' => $textBody = faker()->text(),
            'html_body' => '<p>'
                . faker()->text()
                . '[flux:comment:' . $ticket->getMorphClass() . ':' . $ticket->getKey() . ']</p>',
            'communication_type_enum' => 'mail',
            'date' => now()->format('Y-m-d H:i:s'),
            'tags' => [],
        ]);
        $result = $action->validate()->execute();

        $this->assertNotNull($result);
        $this->assertNotNull($result->id);
        Event::assertDispatched('action.executed: ' . CreateMailMessage::class);

        $listener = new CreateMailExecutedSubscriber();
        $listener->handle($action);

        $this->assertDatabaseHas('comments', [
            'comment' => $textBody,
            'is_internal' => false,
            'created_by' => $this->address->getMorphClass() . ':' . $this->address->getKey(),
        ]);

        $this->assertTrue($ticket->communications()->where('communications.id', $result->id)->exists());
    }
}
