<x-filament::page >
 


<link rel="stylesheet" href="{{ asset('css/order-styles.css') }}">

<div class="main-container" wire:poll.3s>
  <div class="sections-wrapper">

    <!-- Pending Orders Section -->
    <div class="section">
      <div class="section-container">
        <div class="section-header pending-header">
          <div class="header-content">
            <div class="header-left">
              <div class="icon-container">
                <svg class="icon" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6.5a1.5 1.5 0 01-1.5 1.5h-9A1.5 1.5 0 014 11.5V5z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h2 class="section-title">{{ __('strings.pending_orders') }}</h2>
            </div>
            <div class="count-badge">
              <span class="count-text">{{ $pendingOrders->count() }}</span>
            </div>
          </div>
        </div>

        <div class="section-content">
          <div class="cards-grid">
            @forelse($pendingOrders as $order)
            <div class="order-card pending-card">
              <div class="card-header">
                <h3 class="order-id pending-id">#{{ $order->id }}</h3>
                <span class="status-badge pending-badge">
                  {{ __('strings.' . $order->status) }}
                </span>
              </div>
              
              
              
              <div class="card-actions">
                {{-- {{ ($this->changeStatusAction)(['order_id' => $order->id, 'current_status' => $order->status]) }} --}}
              </div>
            </div>
            @empty
            <div class="empty-state">
              <div class="empty-icon">📭</div>
              <p class="empty-text">{{ __('strings.no_pending_orders') }}</p>
            </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <!-- Completed Orders Section -->
    <div class="section">
      <div class="section-container">
        <div class="section-header completed-header">
          <div class="header-content">
            <div class="header-left">
              <div class="icon-container">
                <svg class="icon" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
              <h2 class="section-title">{{ __('strings.completed_orders') }}</h2>
            </div>
            <div class="count-badge">
              <span class="count-text">{{ $completedOrders->count() }}</span>
            </div>
          </div>
        </div>

        <div class="section-content">
          <div class="cards-grid">
            @forelse($completedOrders as $order)
            <div class="order-card completed-card">
              <div class="card-header">
                <h3 class="order-id completed-id">#{{ $order->id }}</h3>
                <span class="status-badge completed-badge">
                  {{ __('strings.' . $order->status) }}
                </span>
              </div>
              
             
              
              <div class="card-actions">
                {{-- {{ ($this->changeStatusAction)(['order_id' => $order->id, 'current_status' => $order->status]) }} --}}
              </div>
            </div>
            @empty
            <div class="empty-state">
              <div class="empty-icon">🎉</div>
              <p class="empty-text">{{ __('strings.no_completed_orders') }}</p>
            </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<x-filament-actions::modals />
</x-filament::page>