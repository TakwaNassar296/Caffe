# Scheduled Orders System - Architecture Documentation

## Overview

This document describes the refactored scheduled orders system that allows users to create orders with a future scheduled date/time. When the scheduled time arrives, orders are automatically moved from 'scheduled' to 'pending' status using background jobs and Laravel's task scheduler.

## Architecture

### Components

```
┌─────────────────────────────────────────────────────────────┐
│                    Scheduled Orders System                   │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
┌─────────────────┐   ┌──────────────┐   ┌────────────────┐
│   Controller    │   │   Service    │   │    Job Queue   │
│  (HTTP Layer)  │──▶│ Calculation  │   │   (Background)  │
└─────────────────┘   └──────────────┘   └────────────────┘
                              │                     │
                              │                     ▼
                              │           ┌──────────────────┐
                              │           │   Task Scheduler │
                              │           │  (Every Minute)  │
                              │           └──────────────────┘
                              │                     │
                              ▼                     ▼
                    ┌──────────────────────────────────────┐
                    │         Database (MySQL/SQLite)     │
                    │    - orders (status, schedual_date) │
                    │    - order_items                   │
                    └──────────────────────────────────────┘
```

### Workflow

```
User Creates Order
        │
        ▼
[Order Created: status = 'scheduled']
        │
        │ (waits until scheduled time)
        │
        ▼
[Every Minute: ProcessScheduledOrdersJob runs]
        │
        ▼
[If time <= now: status = 'pending']
        │
        ▼
[Order enters normal processing flow]
```

## Components in Detail

### 1. OrderSecheualController (`app/Http/Controllers/Api/OrderSecheualController.php`)

**Responsibilities:**
- Handle HTTP requests for scheduled orders
- Validate business rules (branch hours, product availability)
- Coordinate order creation process
- Provide endpoints for CRUD operations on scheduled orders

**Key Methods:**
- `scheduleOrder()` - Create a new scheduled order
- `index()` - List all user's scheduled orders
- `readScheduledOrder($orderId)` - Retrieve specific scheduled order
- `deleteScheduledOrder($orderId)` - Cancel a scheduled order
- `getOrderSummary()` - Get order totals and calculations
- `checkout($orderId)` - Mark order as paid

**Dependencies:**
- `OrderCalculationService` - For business logic calculations
- `Order`, `Product`, `Branch` models
- Database transaction management

### 2. ProcessScheduledOrdersJob (`app/Jobs/ProcessScheduledOrdersJob.php`)

**Responsibilities:**
- Process orders whose scheduled time has arrived
- Update order status from 'scheduled' to 'pending'
- Handle errors and logging
- Scale efficiently for large volumes

**Features:**
- Runs every minute via Laravel's scheduler
- Uses query scopes for readability (`Order::scheduled()->due()`)
- Processes orders in chunks of 100 for memory efficiency
- Includes comprehensive error handling and logging
- Retry logic (3 attempts with 60-second backoff)

**Edge Cases Handled:**
- Timezone consistency (uses Carbon for accurate time handling)
- Multiple orders scheduled for the same time
- Failed processing attempts (continues with other orders)
- Memory efficiency for thousands of orders

### 3. OrderCalculationService (`app/Services/OrderCalculationService.php`)

**Responsibilities:**
- Encapsulate all order calculation logic
- Separate business logic from controllers
- Ensure reusability and testability

**Key Methods:**
- `calculateCharges($subtotal)` - Calculate delivery charge and tax
- `calculateFinalTotal()` - Calculate total order amount
- `calculateDiscount()` - Apply coupon discounts
- `calculateRewards()` - Calculate user points and stars

**Benefits:**
- DRY (Don't Repeat Yourself) principle
- Single source of truth for calculations
- Easier to test and maintain
- Can be reused across different controllers

### 4. Query Scopes (`app/Models/Order.php`)

**Added Scopes:**
- `scheduled()` - Filter orders with status 'scheduled'
- `due($now)` - Filter orders whose schedule time has passed
- `pending()` - Filter orders with status 'pending'
- `completed()` - Filter completed orders

**Benefits:**
- Improved code readability
- Reduced duplication
- Easier to maintain query logic

**Usage:**
```php
// Before
$orders = Order::where('status', 'scheduled')
    ->whereNotNull('schedual_date')
    ->where('schedual_date', '<=', now())
    ->get();

// After
$orders = Order::scheduled()->due()->get();
```

### 5. Task Scheduler (`routes/console.php`)

**Configuration:**
- Runs every minute
- Dispatches `ProcessScheduledOrdersJob`
- Prevents overlapping executions (`withoutOverlapping()`)
- Runs in background for better performance

**Setup Required:**
```bash
# Add to crontab (production)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# Manual testing
php artisan schedule:run

# Test specific task
php artisan schedule:test
```

## API Endpoints

### User Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/orders/schedule` | Create a new scheduled order |
| GET | `/api/orders/schedule` | List all scheduled orders |
| GET | `/api/orders/schedule/{id}` | Get specific scheduled order |
| DELETE | `/api/orders/schedule/{id}` | Cancel scheduled order |
| GET | `/api/orders/schedule/summary` | Get order summary |
| POST | `/api/orders/schedule/{id}/checkout` | Complete payment |

### Request Example

```json
POST /api/orders/schedule
{
  "order_type": "delivery",
  "branch_id": 1,
  "schedual_date": "2025-01-15 14:00:00",
  "products": [
    {
      "id": 1,
      "quantity": 2,
      "option_values": [1, 2]
    }
  ],
  "user_location_id": 1,
  "user_payment_id": 1
}
```

### Response Example

```json
{
  "status": true,
  "message": "Order scheduled successfully",
  "data": {
    "id": 123,
    "status": "scheduled",
    "schedual_date": "2025-01-15 14:00:00",
    "total_price": 45.50
  }
}
```

## Database Schema

### Orders Table

```sql
- id
- user_id
- branch_id
- status (enum: 'pending', 'scheduled', 'completed', ...)
- schedual_date (datetime)
- sub_total
- total_price
- tax
- delivery_charge
- type (enum: 'delivery', 'pick_up')
- created_at
- updated_at
```

**Indexes Recommended:**
```sql
CREATE INDEX idx_orders_status_scheduled_date ON orders(status, schedual_date);
```

This index optimizes the query used by ProcessScheduledOrdersJob.

## Scalability Considerations

### Current Architecture

- **Small Scale (< 1000 scheduled orders)**: Uses database queue, processes every minute
- **Medium Scale (1000-10000 orders)**: Chunk processing (100 at a time) handles efficiently
- **Large Scale (10000+ orders)**: Needs Redis queue backend for better performance

### Optimization Strategies

1. **Database Indexing**
   ```sql
   CREATE INDEX idx_orders_status_date ON orders(status, schedual_date);
   ```

2. **Queue Configuration**
   - Use Redis for production: `QUEUE_CONNECTION=redis`
   - Multiple queue workers for parallel processing
   - Horizontal scaling

3. **Processing Optimization**
   - Current: Chunks of 100 orders
   - Can be adjusted based on memory constraints
   - Consider batch processing for very large volumes

4. **Monitoring**
   - Log all scheduled order processing
   - Monitor job failure rates
   - Track processing time per batch

## Timezone Handling

All datetime operations use Carbon with application timezone from `config/app.php`.

```php
// In ProcessScheduledOrdersJob
$now = Carbon::now(); // Uses app timezone automatically
```

## Error Handling

### Job Failures

- **Automatic Retry**: 3 attempts with 60-second backoff
- **Logging**: All failures logged with context
- **Alerts**: Failed jobs logged to `failed_jobs` table

### Controller Errors

- **Transaction Management**: Database transactions ensure data integrity
- **Validation**: Request validation catches errors early
- **Logging**: All errors logged for debugging

## Testing

### Manual Testing

```bash
# Test scheduled job manually
php artisan queue:work

# Test task scheduler
php artisan schedule:run

# Test specific order status update
# In tinker: php artisan tinker
>>> $order = Order::find(1);
>>> $order->update(['schedual_date' => now()->subMinute()]);
>>> ProcessScheduledOrdersJob::dispatch();
```

### Automated Testing

```php
// Example test
public function test_scheduled_order_changes_to_pending()
{
    $order = Order::factory()->create([
        'status' => 'scheduled',
        'schedual_date' => now()->subMinute()
    ]);

    ProcessScheduledOrdersJob::dispatch();

    $order->refresh();
    $this->assertEquals('pending', $order->status);
}
```

## Security Considerations

1. **User Authorization**: All endpoints require `auth:user` middleware
2. **Order Ownership**: Users can only access their own orders
3. **Branch Validation**: Scheduled time validated against branch hours
4. **SQL Injection**: Protected by Eloquent ORM
5. **CSRF Protection**: Applied via middleware

## Monitoring and Observability

### Logs

All operations are logged:
- Order creation
- Job execution start/completion
- Status updates
- Errors and failures

**Log File**: `storage/logs/laravel.log`

### Metrics to Monitor

- Number of scheduled orders processed per minute
- Average processing time
- Job failure rate
- Orders not processed (edge cases)

## Migration Guide

### From Previous Version

If migrating from an older version without scheduled orders:

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Set up cron scheduler:
   ```bash
   crontab -e
   # Add: * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
   ```

3. Start queue worker:
   ```bash
   php artisan queue:work --daemon
   ```

## Future Enhancements

1. **Notification System**: Notify users when scheduled order becomes active
2. **Scheduling UI**: Allow users to view/edit scheduled orders
3. **Bulk Scheduling**: Allow scheduling multiple orders
4. **Recurring Orders**: Support for daily/weekly scheduled orders
5. **Advanced Scheduling**: Calendar integration, availability checking

## Troubleshooting

### Scheduled orders not becoming pending

1. Check if scheduler is running:
   ```bash
   php artisan schedule:list
   ```

2. Check queue worker:
   ```bash
   php artisan queue:work --verbose
   ```

3. Check logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Performance Issues

1. Add database indexes
2. Use Redis queue instead of database
3. Increase chunk size in job (adjust based on memory)
4. Add more queue workers

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review this documentation
- Consult Laravel documentation for scheduler and queues

