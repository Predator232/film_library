-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Апр 07 2025 г., 12:00
-- Версия сервера: 5.7.33-log
-- Версия PHP: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `film_library`
--

CREATE DATABASE IF NOT EXISTS `film_library`;
USE `film_library`;

-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `features` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `subscriptions`
--

INSERT INTO `subscriptions` (`name`, `duration`, `price`, `description`, `features`) VALUES
('Базовый', 30, 299.00, 'Доступ к базовому каталогу фильмов', 'Просмотр фильмов в SD качестве'),
('Стандарт', 30, 599.00, 'Расширенный каталог фильмов', 'Просмотр фильмов в HD качестве, без рекламы'),
('Премиум', 30, 999.00, 'Полный доступ ко всем фильмам', 'Просмотр фильмов в 4K качестве, без рекламы, возможность скачивания');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`username`, `email`, `password`) VALUES
('admin', 'admin@filmoteka.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('user1', 'user1@filmoteka.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('user2', 'user2@filmoteka.ru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- --------------------------------------------------------

--
-- Структура таблицы `user_subscriptions`
--

CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `user_subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_subscription_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `directors`
--

CREATE TABLE IF NOT EXISTS `directors` (
  `director_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birth_date` date DEFAULT NULL,
  `biography` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`director_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `directors`
--

INSERT INTO `directors` (`name`, `birth_date`, `biography`, `country`) VALUES
('Кристофер Нолан', '1970-07-30', 'Известный британско-американский режиссёр, продюсер и сценарист.', 'Великобритания'),
('Пон Чжун-хо', '1969-09-14', 'Южнокорейский режиссёр и сценарист.', 'Южная Корея'),
('Квентин Тарантино', '1963-03-27', 'Американский режиссёр, сценарист и актёр.', 'США');

-- --------------------------------------------------------

--
-- Структура таблицы `genres`
--

CREATE TABLE IF NOT EXISTS `genres` (
  `genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `genres`
--

INSERT INTO `genres` (`name`, `description`) VALUES
('Драма', 'Серьёзные фильмы, сосредоточенные на развитии персонажей и сюжета'),
('Триллер', 'Фильмы, вызывающие напряжение и волнение'),
('Научная фантастика', 'Фильмы о будущем, технологиях и научных теориях'),
('Комедия', 'Лёгкие и весёлые фильмы, созданные для смеха'),
('Боевик', 'Динамичные фильмы с погонями и трюками'),
('Ужасы', 'Фильмы, вызывающие страх и напряжение'),
('Фэнтези', 'Фильмы с волшебством, мифами и вымышленными мирами'),
('Мелодрама', 'Эмоциональные фильмы о любви и переживаниях'),
('Приключения', 'Фильмы о путешествиях и открытиях'),
('Анимация', 'Мультипликационные фильмы для детей и взрослых');

-- --------------------------------------------------------

--
-- Структура таблицы `movies`
--

CREATE TABLE IF NOT EXISTS `movies` (
  `movie_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `release_year` int(11) NOT NULL,
  `director_id` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration` int(11) DEFAULT NULL COMMENT 'Duration in minutes',
  `poster_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `average_rating` decimal(3,1) DEFAULT NULL,
  PRIMARY KEY (`movie_id`),
  FOREIGN KEY (`director_id`) REFERENCES `directors` (`director_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `movies`
--

INSERT INTO `movies` (`title`, `release_year`, `director_id`, `description`, `duration`, `poster_url`, `average_rating`) VALUES
('Начало', 2010, 1, 'Вор проникает в сны людей, чтобы украсть идеи, но получает задание — внедрить мысль.', 148, 'inception.jpg', 8.8),
('Крестный отец', 1972, 2, 'Классическая история о мафиозной семье Корлеоне.', 175, 'godfather.jpg', 9.2),
('Криминальное чтиво', 1994, 3, 'История о преступниках, рассказанная в нелинейном повествовании.', 154, 'pulpfiction.jpg', 8.9),
('Бойцовский клуб', 1999, 3, 'История о мужчине, который создает подпольный бойцовский клуб.', 139, 'fightclub.jpg', 8.8),
('Матрица', 1999, 1, 'Хакер узнает, что реальность - это симуляция.', 136, 'matrix.jpg', 8.7),
('Форрест Гамп', 1994, 2, 'История простого человека, который становится свидетелем важных исторических событий.', 142, 'forrestgump.jpg', 8.8),
('Титаник', 1997, 2, 'История любви на фоне гибели легендарного лайнера.', 195, 'titanic.jpg', 7.8),
('Аватар', 2009, 1, 'История о человеке, который становится частью инопланетной цивилизации.', 162, 'avatar.jpg', 7.8),
('Темный рыцарь', 2008, 1, 'Бэтмен противостоит Джокеру в борьбе за Готэм.', 152, 'darkknight.jpg', 9.0),
('Побег из Шоушенка', 1994, 2, 'История о невиновном банкире, который планирует побег из тюрьмы.', 142, 'shawshank.jpg', 9.3);

-- --------------------------------------------------------

--
-- Структура таблицы `movie_genres`
--

CREATE TABLE IF NOT EXISTS `movie_genres` (
  `movie_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  PRIMARY KEY (`movie_id`,`genre_id`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`),
  FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `movie_genres`
--

INSERT INTO `movie_genres` (`movie_id`, `genre_id`) VALUES
(1, 3), (1, 1), -- Начало: Научная фантастика, Драма
(2, 1), (2, 5), -- Крестный отец: Драма, Боевик
(3, 1), (3, 5), -- Криминальное чтиво: Драма, Боевик
(4, 1), (4, 2), -- Бойцовский клуб: Драма, Триллер
(5, 3), (5, 5), -- Матрица: Научная фантастика, Боевик
(6, 1), (6, 8), -- Форрест Гамп: Драма, Мелодрама
(7, 1), (7, 8), -- Титаник: Драма, Мелодрама
(8, 3), (8, 9), -- Аватар: Научная фантастика, Приключения
(9, 1), (9, 5), -- Темный рыцарь: Драма, Боевик
(10, 1);        -- Побег из Шоушенка: Драма

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `movie_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`news_id`),
  FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `news`
--

INSERT INTO `news` (`title`, `content`, `image_url`, `movie_id`, `created_at`) VALUES
('Новый фильм Кристофера Нолана', 'Кристофер Нолан анонсировал работу над новым фильмом, который выйдет в 2025 году.', 'nolan.jpg', 1, '2025-04-01 10:00:00'),
('Фестиваль кино в Каннах', 'Объявлены даты проведения Каннского кинофестиваля 2025 года.', 'cannes.jpg', NULL, '2025-04-02 11:30:00'),
('Новые технологии в кино', 'Крупные студии начинают использовать искусственный интеллект для создания спецэффектов.', 'tech.jpg', NULL, '2025-04-03 09:15:00');

-- --------------------------------------------------------

--
-- Структура таблицы `user_movies`
--

CREATE TABLE IF NOT EXISTS `user_movies` (
  `user_movie_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `watched` tinyint(1) NOT NULL DEFAULT 0,
  `favorite` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(3,1) DEFAULT NULL,
  `watch_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_movie_id`),
  UNIQUE KEY `user_movie_unique` (`user_id`, `movie_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_movies`
--

INSERT INTO `user_movies` (`user_id`, `movie_id`, `watched`, `favorite`, `rating`, `watch_date`) VALUES
(1, 1, 1, 1, 9.0, '2025-04-01 10:00:00'), -- admin посмотрел и добавил в избранное "Начало"
(1, 3, 1, 0, 8.5, '2025-04-02 15:30:00'), -- admin посмотрел "Криминальное чтиво"
(2, 2, 1, 1, 9.5, '2025-04-03 20:00:00'), -- user1 посмотрел и добавил в избранное "Крестный отец"
(2, 5, 1, 0, 8.0, '2025-04-04 18:45:00'), -- user1 посмотрел "Матрицу"
(3, 1, 1, 1, 9.2, '2025-04-05 14:20:00'); -- user2 посмотрел и добавил в избранное "Начало"

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;