<?php
/**
 * Универсальный footer для всех страниц
 */
?>
<!-- Footer -->
<footer class="bg-slate-900 text-white py-12 mt-20">
    <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between gap-8">
            <div class="space-y-3 max-w-sm">
                <span class="heading-font text-2xl font-bold text-white">Travel Hub</span>
                <p class="text-slate-300">Мы создаём путешествия класса люкс и обеспечиваем сервис, который остаётся в памяти надолго.</p>
                <div class="flex gap-3">
                    <a href="https://t.me/TrevelHub" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-sky-600 hover:text-white transition"><i class="fab fa-telegram"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-pink-600 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                    <a href="https://wa.me/70000000000" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-300 hover:bg-green-600 hover:text-white transition"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm text-slate-300">
                <div>
                    <h3 class="font-semibold text-white mb-3">Компания</h3>
                    <ul class="space-y-2">
                        <li><a href="/frontend/window/about.php" class="hover:text-sky-400">О нас</a></li>
                        <li><a href="/frontend/window/offices.php" class="hover:text-sky-400">Наши офисы</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-3">Услуги</h3>
                    <ul class="space-y-2">
                        <li><a href="/frontend/window/services.php" class="hover:text-sky-400">Все услуги</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-white mb-3">Контакты</h3>
                    <ul class="space-y-2">
                        <li><a href="tel:+74956603666" class="hover:text-sky-400">+7 (495) 660-36-66</a></li>
                        <li><a href="mailto:concierge@travelhub.ru" class="hover:text-sky-400">concierge@travelhub.ru</a></li>
                        <li class="text-slate-300">Москва, Краснопресненская наб., 12</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-400 border-t border-slate-800 pt-8">
            <p>© <?php echo date('Y'); ?> Travel Hub. Все права защищены.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-sky-400">Политика конфиденциальности</a>
                <a href="#" class="hover:text-sky-400">Условия использования</a>
            </div>
        </div>
    </div>
</footer>




