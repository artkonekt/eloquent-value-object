# Eloquent Value Objects


[![MIT Software License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.md)

This package provides support for auto casting value objects to/from scalar
fields fields in
[Eloquent models](https://laravel.com/docs/5.5/eloquent-mutators).

## Installation

`composer require konekt/eloquent-value-object`

## Usage

1. Add the `CastsValueObjects` trait to your model
2. Define the attributes to be casted via the `protected $valueObjects` property on the model

### Example

**The Enum:**

```php
namespace App;

use Konekt\Enum\Enum;

class OrderStatus extends Enum
{
    const __default = self::PENDING;

    const PENDING   = 'pending';
    const CANCELLED = 'cancelled';
    const COMPLETED = 'completed';

}
```

**The Model:**

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Konekt\EloquentValueObject\CastsValueObjects;

class Order extends Model
{
    use CastsValueObjects;

    protected $valueObjects = [
        'status' => OrderStatus::class
    ];
}
```

**Client code:**
```php
$order = Order::create([
    'status' => 'pending'
]);

// The status attribute will be an enum object:
echo get_class($order->status);
// output: App\OrderStatus

echo $order->status->value();
// output: 'pending'

echo $order->status->isPending() ? 'yes' : 'no';
// output: yes

echo $order->status->isCancelled() ? 'yes' : 'no';
// output: no

// You can assign an enum object as attribute value:
$order->status = OrderStatus::COMPLETED();
echo $order->status->value();
// output: 'completed'

// It also works with mass assignment:
$order = Order::create([
    'status' => OrderStatus::COMPLETED()    
]);

echo $order->status->value();
// output 'completed'

// It still accepts scalar values:
$order->status = 'completed';
echo $order->status->isCompleted() ? 'yes' : 'no';
// output: yes

// But it doesn't accept scalar values that aren't in the enum:
$order->status = 'negotiating';
// throws UnexpectedValueException
// Given value (negotiating) is not in enum `App\OrderStatus`
```

### Resolving Class Runtime

It is possible to defer the resolution of value object's class to runtime.

It happens using the `ClassName@method` notation known from Laravel.

This is useful for libraries, so you can 'late-bind' the actual value object
class and let the user to extend it.

#### Example

**The Model:**

```php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Konekt\EloquentValueObject\CastsValueObjects;

class Order extends Model
{
    use CastsValueObjects;

    protected $valueObjects = [
        'status' => 'OrderStatusResolver@enumClass'
    ];
}
```

**The Resolver:**

```php
namespace App;

class OrderStatusResolver
{
    /**
     * Returns the enum class to use as order status enum
     *
     * @return string
     */
    public static function enumClass()
    {
        return config('app.order.status.class', OrderStatus::class);
    }
}
```

This way the value object class becomes configurable without the need to modify the
Model code.
