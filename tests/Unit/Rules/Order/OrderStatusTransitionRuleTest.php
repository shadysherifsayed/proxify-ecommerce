<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Rules\Order\OrderStatusTransitionRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->order = Order::factory()->pending()->create();
});

test('can be instantiated with valid order', function () {
    $rule = new OrderStatusTransitionRule($this->order);

    expect($rule)->toBeInstanceOf(OrderStatusTransitionRule::class);
});

test('throws exception when order has null status', function () {
    $order = new Order;

    expect(fn () => new OrderStatusTransitionRule($order))
        ->toThrow(InvalidArgumentException::class, 'Invalid order or order status.');
});

test('validation passes for valid pending to processing transition', function () {
    $rule = new OrderStatusTransitionRule($this->order);
    $failCallbackCalled = false;

    $fail = function ($message) use (&$failCallbackCalled) {
        $failCallbackCalled = true;
    };

    $rule->validate('status', OrderStatus::PROCESSING->value, $fail);

    expect($failCallbackCalled)->toBeFalse();
});

test('validation passes for valid pending to cancelled transition', function () {
    $rule = new OrderStatusTransitionRule($this->order);
    $failCallbackCalled = false;

    $fail = function ($message) use (&$failCallbackCalled) {
        $failCallbackCalled = true;
    };

    $rule->validate('status', OrderStatus::CANCELLED->value, $fail);

    expect($failCallbackCalled)->toBeFalse();
});

test('validation fails for invalid pending to completed transition', function () {
    $rule = new OrderStatusTransitionRule($this->order);
    $failMessage = null;

    $fail = function ($message) use (&$failMessage) {
        $failMessage = $message;
    };

    $rule->validate('status', OrderStatus::COMPLETED->value, $fail);

    expect($failMessage)->not()->toBeNull();
    expect($failMessage)->toBe("The order cannot transition to the status 'completed'.");
});

test('validation passes for valid processing to completed transition', function () {
    $this->order->update(['status' => OrderStatus::PROCESSING]);
    $rule = new OrderStatusTransitionRule($this->order);
    $failCallbackCalled = false;

    $fail = function ($message) use (&$failCallbackCalled) {
        $failCallbackCalled = true;
    };

    $rule->validate('status', OrderStatus::COMPLETED->value, $fail);

    expect($failCallbackCalled)->toBeFalse();
});

test('validation passes for valid processing to cancelled transition', function () {
    $this->order->update(['status' => OrderStatus::PROCESSING]);
    $rule = new OrderStatusTransitionRule($this->order);
    $failCallbackCalled = false;

    $fail = function ($message) use (&$failCallbackCalled) {
        $failCallbackCalled = true;
    };

    $rule->validate('status', OrderStatus::CANCELLED->value, $fail);

    expect($failCallbackCalled)->toBeFalse();
});

test('validation fails for invalid processing to pending transition', function () {
    $this->order->update(['status' => OrderStatus::PROCESSING]);
    $rule = new OrderStatusTransitionRule($this->order);
    $failMessage = null;

    $fail = function ($message) use (&$failMessage) {
        $failMessage = $message;
    };

    $rule->validate('status', OrderStatus::PENDING->value, $fail);

    expect($failMessage)->not()->toBeNull();
    expect($failMessage)->toBe("The order cannot transition to the status 'pending'.");
});

test('validation fails for completed order transition to any status', function () {
    $this->order->update(['status' => OrderStatus::COMPLETED]);
    $rule = new OrderStatusTransitionRule($this->order);

    $statuses = [
        OrderStatus::PENDING->value,
        OrderStatus::PROCESSING->value,
        OrderStatus::CANCELLED->value,
    ];

    foreach ($statuses as $status) {
        $failMessage = null;

        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $rule->validate('status', $status, $fail);

        expect($failMessage)->not()->toBeNull();
        expect($failMessage)->toBe("The order cannot transition to the status '$status'.");
    }
});

test('validation fails for cancelled order transition to any status', function () {
    $this->order->update(['status' => OrderStatus::CANCELLED]);
    $rule = new OrderStatusTransitionRule($this->order);

    $statuses = [
        OrderStatus::PENDING->value,
        OrderStatus::PROCESSING->value,
        OrderStatus::COMPLETED->value,
    ];

    foreach ($statuses as $status) {
        $failMessage = null;

        $fail = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $rule->validate('status', $status, $fail);

        expect($failMessage)->not()->toBeNull();
        expect($failMessage)->toBe("The order cannot transition to the status '$status'.");
    }
});

test('validation fails for invalid status string', function () {
    $rule = new OrderStatusTransitionRule($this->order);
    $failMessage = null;

    $fail = function ($message) use (&$failMessage) {
        $failMessage = $message;
    };

    $rule->validate('status', 'invalid_status', $fail);

    expect($failMessage)->not()->toBeNull();
    expect($failMessage)->toBe("The order cannot transition to the status 'invalid_status'.");
});

test('all valid transitions pass validation', function (OrderStatus $fromStatus, string $toStatus) {
    $this->order->update(['status' => $fromStatus]);
    $rule = new OrderStatusTransitionRule($this->order);
    $failCallbackCalled = false;

    $fail = function ($message) use (&$failCallbackCalled) {
        $failCallbackCalled = true;
    };

    $rule->validate('status', $toStatus, $fail);

    expect($failCallbackCalled)->toBeFalse("Transition from {$fromStatus->value} to {$toStatus} should be valid");
})->with([
    'pending to processing' => [OrderStatus::PENDING, OrderStatus::PROCESSING->value],
    'pending to cancelled' => [OrderStatus::PENDING, OrderStatus::CANCELLED->value],
    'processing to completed' => [OrderStatus::PROCESSING, OrderStatus::COMPLETED->value],
    'processing to cancelled' => [OrderStatus::PROCESSING, OrderStatus::CANCELLED->value],
]);

test('all invalid transitions fail validation', function (OrderStatus $fromStatus, string $toStatus) {
    $this->order->update(['status' => $fromStatus]);
    $rule = new OrderStatusTransitionRule($this->order);
    $failMessage = null;

    $fail = function ($message) use (&$failMessage) {
        $failMessage = $message;
    };

    $rule->validate('status', $toStatus, $fail);

    expect($failMessage)->not()->toBeNull("Transition from {$fromStatus->value} to {$toStatus} should be invalid");
    expect($failMessage)->toBe("The order cannot transition to the status '{$toStatus}'.");
})->with([
    'pending to completed' => [OrderStatus::PENDING, OrderStatus::COMPLETED->value],
    'processing to pending' => [OrderStatus::PROCESSING, OrderStatus::PENDING->value],
    'completed to pending' => [OrderStatus::COMPLETED, OrderStatus::PENDING->value],
    'completed to processing' => [OrderStatus::COMPLETED, OrderStatus::PROCESSING->value],
    'completed to cancelled' => [OrderStatus::COMPLETED, OrderStatus::CANCELLED->value],
    'cancelled to pending' => [OrderStatus::CANCELLED, OrderStatus::PENDING->value],
    'cancelled to processing' => [OrderStatus::CANCELLED, OrderStatus::PROCESSING->value],
    'cancelled to completed' => [OrderStatus::CANCELLED, OrderStatus::COMPLETED->value],
]);
