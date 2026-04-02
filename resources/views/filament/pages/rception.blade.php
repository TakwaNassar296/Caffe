<x-filament-panels::page>
  
    
<link rel="stylesheet" href="{{ asset('css/rception.css') }}">

    <div class="container">
      

        <div class="cards-grid">
            <!-- Card 1: استقبال الطلبات -->
            <div class="card card-receive">
                <div class="card-icon">
                    📥
                </div>
                <h2>استقبال الطلبات</h2>
                <p>ستلام جميع الطلبات الواردة من المستودع الرئيسي </p>
            <a href="{{ url('admin/branch-materials') }}" class="card-button">
                انتقل إلى الصفحة
            </a>
             </div>

            <!-- Card 2: رفض الطلبات -->
            <div class="card card-reject">
                <div class="card-icon">
                    ❌
                </div>
                <h2>رفض الطلبات</h2>
                <p>إدارة وتتبع الطلبات المرفوضة مع توضيح الأسباب والإجراءات المطلوبة</p>
                <button class="card-button" onclick="handleRejectedOrders()">
                    إدارة المرفوض
                </button>
            </div>

            <!-- Card 3: مواد مهدر -->
            <div class="card card-waste">
                <div class="card-icon">
                    🗑️
                </div>
                <h2>مواد مهدر</h2>
                <p>تتبع وإدارة المواد المهدرة والتالفة مع إحصائيات شاملة لتقليل الفاقد</p>
                <button class="card-button" onclick="manageWaste()">
                    إدارة المهدر
                </button>
            </div>
        </div>
    </div>

    <script>
         
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });

        // تأثير الماوس للكروت
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
 
</x-filament-panels::page>
