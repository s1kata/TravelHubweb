<?php
// Общий компонент header для всех страниц
// Принимает параметр $current_page для подсветки активной страницы
$current_page = $current_page ?? '';
?>
<header class="bg-white/95 backdrop-blur-lg border-b border-sky-100 sticky top-0 z-50 shadow-sm">
    <div class="mx-auto w-full max-w-[100vw] px-3 sm:px-4 md:px-8 lg:px-16 py-3 md:py-4 flex justify-between items-center gap-2 sm:gap-4">
        <div class="flex items-center gap-3">
            <a href="/index.php" class="flex items-center gap-2 sm:gap-3">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 flex items-center justify-center shadow-lg shadow-sky-200/60">
                    <i class="fas fa-plane text-white text-xs sm:text-base"></i>
                </div>
                <span class="heading-font text-lg sm:text-xl md:text-2xl font-bold text-sky-600 tracking-wide">Travel Hub</span>
            </a>
        </div>

        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <a href="/backend/admin/admin.php" class="hidden lg:block bg-gradient-to-r from-rose-300 via-rose-400 to-rose-500 text-white px-5 py-2 rounded-full font-medium shadow-md hover:shadow-lg transition">Админ панель</a>
        <?php else: ?>
            <!-- Desktop flags -->
            <div class="hidden lg:flex items-center gap-4">
                <div class="bg-gradient-to-r from-sky-50 to-blue-50 px-6 py-2.5 rounded-full text-xs uppercase tracking-[0.32em] text-sky-600 font-semibold border border-sky-200 shadow-sm">
                    <a href="/frontend/window/countries/seychelles.php" class="text-slate-600 hover:text-sky-600 mr-4 transition font-medium">🇸🇨 Сейшелы</a>
                    <a href="/frontend/window/countries/turkey.php" class="text-slate-600 hover:text-sky-600 mr-4 transition font-medium">🇹🇷 Турция</a>
                    <a href="/frontend/window/countries/uae.php" class="text-slate-600 hover:text-sky-600 mr-4 transition font-medium">🇦🇪 ОАЭ</a>
                    <a href="/frontend/window/countries/egypt.php" class="text-slate-600 hover:text-sky-600 transition font-medium">🇪🇬 Египет</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="tel:+74951234567" class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center hover:bg-sky-200 transition">
                        <i class="fas fa-phone text-sm"></i>
                    </a>
                    <a href="https://t.me/TrevelHub" class="w-10 h-10 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center hover:bg-sky-200 transition">
                        <i class="fab fa-telegram"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <nav id="desktop-nav" class="hidden lg:flex items-center space-x-4 xl:space-x-8">
            <a href="/index.php" class="text-slate-700 font-medium hover:text-sky-500 transition <?php echo ($current_page === 'home') ? 'text-sky-500' : ''; ?>">Главная</a>
            <div class="relative group">
                <button class="text-slate-700 font-medium hover:text-sky-500 transition flex items-center gap-1 <?php echo ($current_page === 'countries') ? 'text-sky-500' : ''; ?>">
                    Страны
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div class="absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-sky-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 max-h-96 overflow-y-auto">
                    <div class="py-2">
                        <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide border-b border-sky-100">Популярные</div>
                        <a href="/frontend/window/countries/turkey.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇹🇷</span> Турция
                        </a>
                        <a href="/frontend/window/countries/egypt.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇪🇬</span> Египет
                        </a>
                        <a href="/frontend/window/countries/thailand.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇹🇭</span> Таиланд
                        </a>
                        <a href="/frontend/window/countries/uae.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇦🇪</span> ОАЭ
                        </a>
                        <a href="/frontend/window/countries/russia.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇷🇺</span> Россия
                        </a>
                        <a href="/frontend/window/countries/china.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇨🇳</span> Китай
                        </a>
                        <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide border-t border-b border-sky-100 mt-2">Все страны</div>
                        <a href="/frontend/window/countries/abkhazia.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇦🇧</span> Абхазия
                        </a>
                        <a href="/frontend/window/countries/armenia.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇦🇲</span> Армения
                        </a>
                        <a href="/frontend/window/countries/bahrain.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇧🇭</span> Бахрейн
                        </a>
                        <a href="/frontend/window/countries/cuba.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇨🇺</span> Куба
                        </a>
                        <a href="/frontend/window/countries/india.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇮🇳</span> Индия
                        </a>
                        <a href="/frontend/window/countries/indonesia.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇮🇩</span> Индонезия
                        </a>
                        <a href="/frontend/window/countries/jordan.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇯🇴</span> Иордания
                        </a>
                        <a href="/frontend/window/countries/mauritius.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇲🇺</span> Маврикий
                        </a>
                        <a href="/frontend/window/countries/maldives.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇲🇻</span> Мальдивы
                        </a>
                        <a href="/frontend/window/countries/montenegro.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇲🇪</span> Черногория
                        </a>
                        <a href="/frontend/window/countries/oman.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇴🇲</span> Оман
                        </a>
                        <a href="/frontend/window/countries/philippines.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇵🇭</span> Филиппины
                        </a>
                        <a href="/frontend/window/countries/qatar.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇶🇦</span> Катар
                        </a>
                        <a href="/frontend/window/countries/seychelles.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇸🇨</span> Сейшелы
                        </a>
                        <a href="/frontend/window/countries/sri-lanka.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇱🇰</span> Шри-Ланка
                        </a>
                        <a href="/frontend/window/countries/tanzania.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇹🇿</span> Танзания
                        </a>
                        <a href="/frontend/window/countries/tunisia.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇹🇳</span> Тунис
                        </a>
                        <a href="/frontend/window/countries/venezuela.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇻🇪</span> Венесуэла
                        </a>
                        <a href="/frontend/window/countries/vietnam.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <span class="text-xl mr-2">🇻🇳</span> Вьетнам
                        </a>
                    </div>
                </div>
            </div>
            <a href="/frontend/window/services.php" class="text-slate-700 font-medium hover:text-sky-500 transition <?php echo ($current_page === 'services') ? 'text-sky-500' : ''; ?>">Услуги</a>
            <a href="/frontend/window/video-tutorials.php" class="text-slate-700 font-medium hover:text-sky-500 transition <?php echo ($current_page === 'video-tutorials') ? 'text-sky-500' : ''; ?>">Видео об отеле</a>
            <a href="/frontend/window/turkey-vip-hotels.php" class="text-slate-700 font-medium hover:text-sky-500 transition <?php echo ($current_page === 'vip-hotels') ? 'text-sky-500' : ''; ?>">VIP Отели Турции</a>
            <div class="relative group">
                <button class="text-slate-700 font-medium hover:text-sky-500 transition flex items-center gap-1 <?php echo ($current_page === 'about') ? 'text-sky-500' : ''; ?>">
                    О нас
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div class="absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-sky-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 max-h-96 overflow-y-auto">
                    <div class="py-2">
                        <div class="px-4 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wide border-b border-sky-100">О компании</div>
                        <a href="/frontend/window/about.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            О компании
                        </a>
                        <a href="/frontend/window/offices.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            Наши офисы
                        </a>
                    </div>
                </div>
            </div>
            <a href="/frontend/window/contacts.php" class="text-slate-700 font-medium hover:text-sky-500 transition <?php echo ($current_page === 'contacts') ? 'text-sky-500' : ''; ?>">Контакты</a>
        </nav>

        <div class="flex items-center space-x-4">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="relative" style="z-index: 9999;">
                    <button id="user-menu-button" class="hidden md:flex items-center bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white px-5 py-2 rounded-full font-medium shadow-md hover:shadow-lg transition">
                        <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Пользователь'); ?>
                        <i class="fas fa-chevron-down ml-2"></i>
                    </button>
                    <div id="user-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-sky-100" style="z-index: 10000; min-width: 200px;">
                        <a href="/frontend/window/dashboard.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition rounded-t-xl">
                            <i class="fas fa-tachometer-alt mr-2 text-sky-500"></i>Личный кабинет
                        </a>
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="/backend/admin/admin.php" class="block px-4 py-3 text-sm text-slate-700 hover:bg-sky-50 transition">
                            <i class="fas fa-cog mr-2 text-rose-500"></i>Админ панель
                        </a>
                        <?php endif; ?>
                        <div class="border-t border-sky-100 my-1"></div>
                        <a href="/backend/scripts/logout.php" class="block px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition rounded-b-xl">
                            <i class="fas fa-sign-out-alt mr-2"></i>Выход
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/frontend/window/registration-desktop.php" class="hidden md:block bg-sky-100 text-sky-600 px-5 py-2 rounded-full font-medium border border-sky-200 mr-3">Регистрация</a>
                <a href="/frontend/window/login-desktop.php" class="hidden md:block bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white px-5 py-2 rounded-full font-medium shadow-md animated-button">Войти</a>
            <?php endif; ?>
            <button id="mobile-menu-button" class="md:hidden text-slate-500">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>
    </div>
    
    <div id="mobile-menu" class="md:hidden bg-white/95 border-t border-sky-100 py-4 px-4" style="max-height: 0; opacity: 0; transform: translateY(-12px); overflow: hidden; pointer-events: none; transition: max-height 0.45s ease, opacity 0.3s ease, transform 0.3s ease;">
        <div class="flex flex-col space-y-3">
            <a href="/index.php" class="text-slate-700 font-medium hover:text-sky-500 transition">Главная</a>
            <a href="/frontend/window/countries-list.php" class="text-slate-700 font-medium hover:text-sky-500 transition">Страны</a>
            <a href="/frontend/window/services.php" class="text-slate-700 font-medium hover:text-sky-500 transition">Услуги</a>
            <a href="/frontend/window/video-tutorials.php" class="text-slate-700 font-medium hover:text-sky-500 transition">Видео об отеле</a>
            <a href="/frontend/window/turkey-vip-hotels.php" class="text-slate-700 font-medium hover:text-sky-500 transition">VIP Отели</a>
            <a href="/frontend/window/about.php" class="text-slate-700 font-medium hover:text-sky-500 transition">О нас</a>
            <a href="/frontend/window/offices.php" class="text-slate-700 font-medium hover:text-sky-500 transition">Наши офисы</a>
            <a href="/frontend/window/contacts.php" class="text-slate-700 font-medium hover:text-sky-500 transition">Контакты</a>
            <div class="flex space-x-3 pt-2">
                <a href="tel:+74956603666" class="w-9 h-9 rounded-full border border-sky-200 flex items-center justify-center text-slate-500 hover:bg-sky-100 hover:text-sky-500 transition">
                    <i class="fas fa-phone"></i>
                </a>
                <a href="https://t.me/TrevelHub" class="w-9 h-9 rounded-full border border-sky-200 flex items-center justify-center text-slate-500 hover:bg-sky-100 hover:text-sky-500 transition">
                    <i class="fab fa-telegram"></i>
                </a>
            </div>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="/frontend/window/dashboard.php" class="bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white px-5 py-2 rounded-full font-medium text-center animated-button">Личный кабинет</a>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="/backend/admin/admin.php" class="bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white px-5 py-2 rounded-full font-medium text-center animated-button">Админ панель</a>
                <?php endif; ?>
                <a href="/backend/scripts/logout.php" class="bg-slate-200 text-slate-600 px-5 py-2 rounded-full font-medium text-center">Выход</a>
            <?php else: ?>
                <a href="/frontend/window/login.html" class="bg-gradient-to-r from-sky-300 via-sky-400 to-sky-500 text-white px-5 py-2 rounded-full font-medium text-center animated-button mb-2">Войти</a>
                <a href="/frontend/window/registration.html" class="bg-sky-100 text-sky-600 px-5 py-2 rounded-full font-medium text-center border border-sky-200">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
// Mobile menu toggle
(function() {
    const menuButton = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');
    if (!menuButton || !menu) return;

    menuButton.addEventListener('click', () => {
        const isOpen = menu.style.maxHeight && menu.style.maxHeight !== '0px';
        if (isOpen) {
            menu.style.maxHeight = '0';
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(-12px)';
            menu.style.pointerEvents = 'none';
        } else {
            menu.style.maxHeight = '600px';
            menu.style.opacity = '1';
            menu.style.transform = 'translateY(0)';
            menu.style.pointerEvents = 'auto';
        }
    });

    document.querySelectorAll('#mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            menu.style.maxHeight = '0';
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(-12px)';
            menu.style.pointerEvents = 'none';
        });
    });
})();

// User menu toggle
<?php if(isset($_SESSION['user_id'])): ?>
(function() {
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');
    
    if (userMenuButton && userMenu) {
        let isMenuOpen = false;
        
        userMenuButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            isMenuOpen = !isMenuOpen;
            
            if (isMenuOpen) {
                userMenu.classList.remove('hidden');
            } else {
                userMenu.classList.add('hidden');
            }
        });
        
        // Закрытие меню при клике вне его
        document.addEventListener('click', function(e) {
            if (isMenuOpen && userMenu && userMenuButton) {
                if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                    userMenu.classList.add('hidden');
                    isMenuOpen = false;
                }
            }
        });
    }
})();
<?php endif; ?>

// Глобально ускоряем загрузку медиа
document.addEventListener('DOMContentLoaded', () => {
    // Картинки: принудительно eager и auto decoding
    document.querySelectorAll('img').forEach(img => {
        img.loading = 'eager';
        img.decoding = 'auto';
        if (!img.width && img.naturalWidth) img.width = img.naturalWidth;
        if (!img.height && img.naturalHeight) img.height = img.naturalHeight;
        // Универсальный fallback, если источник недоступен
        img.onerror = function() {
            if (this.dataset.fallbackApplied) return;
            this.dataset.fallbackApplied = '1';
            this.src = '/frontend/window/img/hotels/default.jpg';
        };
    });
    // Iframes — лениво, чтобы не блокировать
    document.querySelectorAll('iframe:not([loading])').forEach(iframe => {
        iframe.loading = 'lazy';
        if (!iframe.referrerPolicy) iframe.referrerPolicy = 'no-referrer-when-downgrade';
    });
});

</script>



