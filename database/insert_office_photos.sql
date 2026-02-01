-- Скрипт для добавления фотографий офисов в базу данных
-- Сначала найдем ID офисов Самары и Москвы

-- Вставляем фотографии для Самары (предполагаем, что office_id = 1 для Самары)
-- Замените office_id на реальный ID офиса Самары из вашей базы данных

INSERT OR IGNORE INTO office_photos (office_id, image_url, title, description, sort_order) VALUES
(1, '/frontend/window/img/offices/samara/12.jpg', 'Фото офиса Самара 1', 'Фотография офиса в Самаре', 1),
(1, '/frontend/window/img/offices/samara/13.jpg', 'Фото офиса Самара 2', 'Фотография офиса в Самаре', 2),
(1, '/frontend/window/img/offices/samara/14.jpg', 'Фото офиса Самара 3', 'Фотография офиса в Самаре', 3),
(1, '/frontend/window/img/offices/samara/15.jpg', 'Фото офиса Самара 4', 'Фотография офиса в Самаре', 4),
(1, '/frontend/window/img/offices/samara/17.jpg', 'Фото офиса Самара 5', 'Фотография офиса в Самаре', 5),
(1, '/frontend/window/img/offices/samara/18.jpg', 'Фото офиса Самара 6', 'Фотография офиса в Самаре', 6),
(1, '/frontend/window/img/offices/samara/19.jpg', 'Фото офиса Самара 7', 'Фотография офиса в Самаре', 7),
(1, '/frontend/window/img/offices/samara/20.jpg', 'Фото офиса Самара 8', 'Фотография офиса в Самаре', 8),
(1, '/frontend/window/img/offices/samara/696b634720956_1.jpg', 'Фото офиса Самара 9', 'Фотография офиса в Самаре', 9),
(1, '/frontend/window/img/offices/samara/696b6347411bc_2.jpg', 'Фото офиса Самара 10', 'Фотография офиса в Самаре', 10),
(1, '/frontend/window/img/offices/samara/696b634751325_3.jpg', 'Фото офиса Самара 11', 'Фотография офиса в Самаре', 11),
(1, '/frontend/window/img/offices/samara/696b6347617be_4.jpg', 'Фото офиса Самара 12', 'Фотография офиса в Самаре', 12);

-- Для Москвы добавьте аналогично, заменив office_id на ID офиса Москвы
-- INSERT OR IGNORE INTO office_photos (office_id, image_url, title, description, sort_order) VALUES
-- (2, '/frontend/window/img/offices/moscow/photo1.jpg', 'Фото офиса Москва 1', 'Фотография офиса в Москве', 1);