-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2022 at 08:53 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tickets-heaven`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `continents`
--

CREATE TABLE `continents` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `continents`
--

INSERT INTO `continents` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Europe', '2022-12-21 15:51:29', '2022-12-21 15:51:29'),
(2, 'Asia', '2022-12-21 15:51:29', '2022-12-21 15:51:29');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `continent_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `continent_id`, `created_at`, `updated_at`) VALUES
(1, 'Bulgaria', 1, '2022-12-21 15:51:39', '2022-12-21 15:51:39'),
(2, 'Germany', 1, '2022-12-21 15:51:39', '2022-12-21 15:51:39'),
(3, 'South Korea', 2, '2022-12-21 15:51:39', '2022-12-21 15:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `created_at`, `updated_at`) VALUES
(1, 'BGN', '2022-12-21 18:01:00', '2022-12-21 18:01:00'),
(2, 'EUR', '2022-12-21 18:01:00', '2022-12-21 18:01:00'),
(3, 'KRW', '2022-12-21 18:01:00', '2022-12-21 18:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `location` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_time` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `end_date` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `end_time` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `host_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `event_picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner_approved` tinyint(1) DEFAULT NULL,
  `currency_id` int(11) NOT NULL,
  `ticket_price` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `name`, `description`, `location`, `start_date`, `start_time`, `end_date`, `end_time`, `host_id`, `venue_id`, `event_picture`, `owner_approved`, `currency_id`, `ticket_price`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 'Дара Екимова - премиера на EP', 'Дара Екимова кани всички на представянето на дебютното й EP \"После Ще Му Мислиш\" в бар Петък :)', NULL, '27.01.2023', '21:00', '27.01.2023', '22:00', 10, 1, '/uploads/event-pictures/3.jpg', 1, 1, 15.00, '2022-12-22 16:15:21', '2022-12-22 16:22:24', NULL),
(4, 'Дара - Родена Такава', 'След дългогодишен успех на музикалната сцена Дара представя първия си албум наречен \"Родена Такава\", включващ песни като \"К\'во Не Чу\" и \"Родена Такава\".', 'ул. „Акад. Стефан Младенов“ 3, 1700 ж.к. Студентски град, София', '11.04.2023', '20:00', '11.04.2023', '21:00', 10, 5, '/uploads/event-pictures/4.jpg', 1, 1, 25.00, '2022-12-22 16:29:00', '2022-12-22 16:29:00', NULL),
(5, 'Графа LIVE', 'За пореден път Графа Ви кани на зашеметяващ концерт, на който ще изпълни всичките си хитове + няколко, нечувани досега, парчета!', NULL, '01.03.2023', '22:00', '02.03.2023', '00:00', 8, 4, '/uploads/event-pictures/5.jpg', 1, 1, 20.00, '2022-12-22 16:34:50', '2022-12-22 16:34:50', NULL),
(6, 'Михаела Филева - Концерт \"Ин И Ян\"', 'След като представи последния си албум, Михаела Филева в готова да започне турнето из цялата страна, започвайки от Клуб Терминал 1 в София.', NULL, '10.02.2023', '19:00', '10.02.2023', '20:30', 8, 4, '/uploads/event-pictures/6.jpg', 1, 1, 25.00, '2022-12-22 16:38:30', '2022-12-22 16:39:21', NULL),
(7, 'Михаела Маринова - \"До Безкрай\" - Концерт', '2 години след като издаде дебютния си албум, Михаела е готова с още невероятна музика, която няма търпение да сподели с всички на живо <3', 'В подлеза на НДК', '27.02.2023', '20:00', '27.02.2023', '21:30', 10, 7, '/uploads/event-pictures/7.jpg', 1, 1, 30.00, '2022-12-22 16:42:40', '2022-12-22 17:25:40', NULL),
(8, 'Мария Илиева - Концерт', 'Мария Илиева кани всички столичани на своя концерт в зала 1 на НДК.', 'Зала 1', '30.05.2023', '18:00', '30.05.2023', '20:00', 9, 6, '/uploads/event-pictures/8.jpg', NULL, 1, 30.00, '2022-12-22 17:24:42', '2022-12-22 17:24:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_participants`
--

CREATE TABLE `event_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `artist_approved` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `event_participants`
--

INSERT INTO `event_participants` (`id`, `event_id`, `user_id`, `artist_approved`, `created_at`, `updated_at`) VALUES
(1, 1, 11, NULL, '2022-12-22 16:12:43', '2022-12-22 16:12:43'),
(2, 1, 17, NULL, '2022-12-22 16:12:43', '2022-12-22 16:12:43'),
(3, 2, 11, NULL, '2022-12-22 16:12:56', '2022-12-22 16:12:56'),
(4, 2, 17, NULL, '2022-12-22 16:12:56', '2022-12-22 16:12:56'),
(5, 3, 11, 1, '2022-12-22 16:15:21', '2022-12-22 16:23:09'),
(6, 3, 17, NULL, '2022-12-22 16:15:21', '2022-12-22 16:15:21'),
(7, 4, 12, 1, '2022-12-22 16:29:00', '2022-12-22 16:29:06'),
(8, 4, 14, 1, '2022-12-22 16:29:00', '2022-12-22 16:29:06'),
(9, 5, 13, 1, '2022-12-22 16:34:50', '2022-12-22 16:34:56'),
(10, 5, 15, 1, '2022-12-22 16:34:50', '2022-12-22 16:34:56'),
(11, 6, 15, 1, '2022-12-22 16:38:30', '2022-12-22 16:39:49'),
(12, 7, 14, 0, '2022-12-22 16:42:40', '2022-12-22 16:43:22'),
(13, 7, 16, 1, '2022-12-22 16:42:40', '2022-12-22 16:42:45'),
(14, 8, 18, NULL, '2022-12-22 17:24:42', '2022-12-22 17:24:42');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20220801184528, 'CreateUsersTable', '2022-12-21 15:49:19', '2022-12-21 15:49:19', 0),
(20220812161018, 'CreateUsersPermissionsTable', '2022-12-21 15:49:19', '2022-12-21 15:49:19', 0),
(20220829152824, 'CreateVenuesTable', '2022-12-21 15:49:19', '2022-12-21 15:49:20', 0),
(20220903071655, 'CreatePhoneCodesTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20220906100213, 'CreateEventsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20220908180633, 'CreateEventParticipantsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20220908180643, 'CreateOrdersTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20220919171655, 'CreateCommentsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221029160755, 'AddOwnerApprovedColumnToEventsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221029160800, 'AddArtistApprovedColumnToEventParticipantsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221120124655, 'CreateCurrenciesTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221120125200, 'AddCurrencyIdColumnToEventsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221120125400, 'AddTicketPriceColumnToEventsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121121400, 'AddDefaultCurrencyIdColumnToUsersTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121144200, 'AddDeletedAtColumnToVenuesTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121150443, 'CreateContinentsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121150543, 'CreateCountriesTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121151000, 'DropCountryAndContinentColumnsFromPhoneCodesTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121151300, 'AddCountryIdColumnToPhoneCodesTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221121193500, 'AddCreditCardNumberColumnToUsersTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221122211100, 'AddTicketPriceColumnToOrdersTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221122212500, 'AddCurrencyIdColumnToOrdersTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221124182628, 'CreateSupportTicketsTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221130190000, 'AddSettingsColumnToUsersTable', '2022-12-21 15:49:20', '2022-12-21 15:49:20', 0),
(20221209175228, 'CreatePromoCodesTable', '2022-12-21 15:49:34', '2022-12-21 15:49:34', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ticket_price` double(8,2) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `ticket_quantity` int(11) NOT NULL,
  `tickets` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `event_id`, `user_id`, `ticket_price`, `currency_id`, `ticket_quantity`, `tickets`, `created_at`, `updated_at`) VALUES
(1, 7, 1, 30.00, 1, 2, '{\"user\":1,\"event\":7,\"venue\":7,\"date\":\"22.12.2022 20:41:04\",\"promo_event\":7,\"promo_percent\":5,\"tickets\":2}', '2022-12-22 18:41:05', '2022-12-22 18:41:05'),
(2, 4, 1, 25.00, 1, 1, '{\"user\":1,\"event\":4,\"venue\":5,\"date\":\"22.12.2022 20:41:45\",\"tickets\":1}', '2022-12-22 18:41:45', '2022-12-22 18:41:45');

-- --------------------------------------------------------

--
-- Table structure for table `phone_codes`
--

CREATE TABLE `phone_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `phone_codes`
--

INSERT INTO `phone_codes` (`id`, `code`, `country_id`, `created_at`, `updated_at`) VALUES
(1, '+359', 1, '2022-12-21 15:51:52', '2022-12-21 15:51:52'),
(2, '+49', 2, '2022-12-21 15:51:52', '2022-12-21 15:51:52'),
(3, '+82', 3, '2022-12-21 15:51:52', '2022-12-21 15:51:52');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `percent` double(8,2) NOT NULL,
  `deadline` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `event_id`, `code`, `percent`, `deadline`, `created_at`, `updated_at`) VALUES
(1, 7, 'tY79Cm', 5.00, '2023-02-25 21:59:00', '2022-12-22 18:25:17', '2022-12-22 18:25:17');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `guest_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `guest_info`, `subject`, `message`, `created_at`, `updated_at`) VALUES
(1, 21, NULL, 'Cannot buy a ticket for an event', 'Hello,\nWhenever I click the checkout button I don\'t receive any tickets in my email, however they do appear in my profile on the website. Can you check if there is a problem with emails or it\'s just me, please?\n\nGreetings,\nNadya', '2022-12-23 17:15:32', '2022-12-23 17:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone_code_id` int(11) DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `credit_card_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_currency_id` int(11) NOT NULL,
  `address` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile_picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `settings` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL,
  `active_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `recover_hash` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_identifier` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `github_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `first_name`, `last_name`, `phone_code_id`, `phone_number`, `credit_card_number`, `default_currency_id`, `address`, `description`, `password`, `profile_picture`, `settings`, `active`, `active_hash`, `recover_hash`, `remember_identifier`, `remember_token`, `github_id`, `facebook_id`, `created_at`, `updated_at`) VALUES
(1, 'User1001260707', 'spiderman07@abv.bg', 'Moni', 'Mihailov', NULL, NULL, NULL, 0, NULL, NULL, '$2y$10$NpndVfJFQzvwvtvpiKGSrOUhq30Dg4FHVAh7TUkdpu7NTfABxzdhu', '/uploads/profile-pictures/1.jpg', '{\"currency\":\"3\",\"email\":{\"user\":{\"hostChanged\":\"1\",\"hostDeleted\":\"1\",\"venueSet\":\"1\",\"eventUpdatedHost\":\"1\",\"venueDeleted\":\"1\",\"eventDeleted\":\"1\",\"ownerApproved\":\"1\",\"ownerRejected\":\"1\",\"artistApproved\":\"1\",\"artistRejected\":\"1\"},\"owner\":{\"venueDeleted\":\"0\",\"eventDeleted\":\"0\",\"artistApproved\":\"0\",\"artistRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"eventAddRequested\":\"0\"},\"host\":{\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"ownerSet\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"artistApproved\":\"0\",\"artistRejected\":\"0\",\"artistDeleted\":\"0\",\"eventAdded\":\"0\",\"eventUpdatedAdmin\":\"0\"},\"artist\":{\"ownerSet\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"hostChanged\":\"0\",\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"artistPending\":\"0\"}}}', 1, NULL, NULL, NULL, NULL, NULL, '5606151706116036', '2022-12-21 15:50:46', '2022-12-25 18:27:41'),
(2, 'owner1', 'koragg@abv.bg', 'Михаил', 'Василев', 1, '888472900', '123456789012345', 1, 'ж.к. Люлин 5, София, България', 'Собственик на Бар Петък', '$2y$10$KwR66b/CCD3cRP/iEgLPXODm.qfyhtY4CjJINb23Yg7YY.vDCet0i', '/uploads/profile-pictures/2.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 16:00:18', '2022-12-21 16:02:25'),
(3, 'owner2', '97koragg97@abv.bg', 'Michael', 'Knight', 2, '466987234', '123451234567890', 2, 'Budapester Strasse 33, Germany', 'Owner of Joy Station', '$2y$10$SWj1RsEE/2C2T.qNNHkv/uYjsSSBywWXUXUJ71eA4MRnm8LmBcque', '/uploads/profile-pictures/3.jpg', '{\"currency\":\"2\",\"email\":{\"user\":{\"hostChanged\":\"1\",\"hostDeleted\":\"1\",\"venueSet\":\"0\",\"eventUpdatedHost\":\"1\",\"venueDeleted\":\"1\",\"eventDeleted\":\"1\",\"ownerApproved\":\"1\",\"ownerRejected\":\"0\",\"artistApproved\":\"1\",\"artistRejected\":\"1\"},\"owner\":{\"venueDeleted\":\"1\",\"eventDeleted\":\"0\",\"artistApproved\":\"1\",\"artistRejected\":\"0\",\"hostDeleted\":\"1\",\"hostSet\":\"0\",\"artistDeleted\":\"1\",\"eventAddRequested\":\"1\"},\"host\":{\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"ownerSet\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"artistApproved\":\"0\",\"artistRejected\":\"0\",\"artistDeleted\":\"0\",\"eventAdded\":\"0\",\"eventUpdatedAdmin\":\"0\"},\"artist\":{\"ownerSet\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"hostChanged\":\"0\",\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"artistPending\":\"0\"}}}', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 16:05:06', '2022-12-21 16:35:24'),
(4, 'owner3', 'venom9797@abv.bg', 'Victor', 'Brown', 2, '987357108', '0987654321098765', 2, 'Brandenburgische Straße 71, Germany', 'Owner of Joy Station', '$2y$10$HvNZtkJul.N0K4md.SeEiOWy5T0ad/w0RywX88i3VlWQ1m9evSoW2', '/uploads/profile-pictures/4.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 16:09:12', '2022-12-21 16:11:06'),
(5, 'owner4', 'asda@das.bg', 'Seok-jin', 'Kim', 3, '790256965', '123450987612345', 3, '273-10, Geumgyeri, Geundeok-myeon', 'Owner of Sofia Live Club', '$2y$10$jz7CnF0JFghl5yF9JwLLUOnsrQLSZykEQcRuWltFXVAo4xYytQjZO', '/uploads/profile-pictures/5.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 16:14:20', '2022-12-21 16:36:10'),
(6, 'owner5', 'dsadas@yruyr.bg', 'Владимир', 'Панайотов', 1, '887368108', '123450987624086', 1, ' ж.к. Дианабад, Sofia, бл.48,', 'Owner of NDK', '$2y$10$mybE0JjDP5UPbhu8yG6gneQMnLqNrP2cffppZU3wpcahdDZffwsGS', '/uploads/profile-pictures/6.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 16:17:09', '2022-12-21 16:47:48'),
(8, 'host1', 'adsa@te.bg', 'Monte', 'Music', 1, '888330990', '1234567890098765', 1, '63, ul. \"Dunav\" str, 1202 Sofia', '„Монте Мюзик ООД“ е музикална компания, създадена по идея на популярния певец Владимир Ампов – Графа и дългогодишният му PR и мениджър Магдалена Сотирова, с която са управляващи партньори във фирмата.', '$2y$10$1MjO0BixJ7TGuV.rTo/.8O/bpMm.huRe5HkEjgucYC8VA4l7YOWda', '/uploads/profile-pictures/8.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 18:02:54', '2022-12-21 18:07:49'),
(9, 'host2', 'daf@tews.bg', 'Stereo', 'Room', 1, '885887221', '123456789045690', 1, 'bul. \"Istoriya slavyanobalgarska\" 17, 1220 Orlandovtsi, Sofia', 'Стерео стая е продуцентски лейбъл за нова българска музика, създаден от Мария Илиева през 2007 година\nСтартира с национално прослушване на таланти, а целта му е да промотира млади изпълнители с творчески дух, ярко и харизматично излъчване\nСтерео стая развива своите артисти и музикални проекти в различни концептуални направления в стиловете поп, поп-рок, r&b и соул, вокален хаус и чил аут\nНякои от най-авторитетните музикални творци в България участват в проектите на лейбъла\nВ бутиковия каталог на Стерео стая присъстват: Криста, B.O.Y.A.N., KNAS – всеки от тях със своя неповторим талант, подчертана индивидуалност и собствен стил в музиката.', '$2y$10$zVIS3XBPk0v9FnK9xnwcLuNolJBbI72Idvs8FfxAmUGaD8yKGJMy2', '/uploads/profile-pictures/9.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 18:10:39', '2022-12-21 18:14:17'),
(10, 'host3', 'gag@test.bf', 'Virginia', 'Records', 1, '886665051', '123450987629603', 1, 'ul. \"Pop Bogomil\" 9, 1202 Sofia Center, Sofia', 'Вирджиния Рекърдс е основана през 1991 г. от Майкъл Кунстман и Станислава Армутлиева, за да бъде партньор в България на най-голямата по онова време музикална компания в света – PolyGram, и така слага началото на легалния музикален бизнес у нас. През 1999 г. PolyGram бива закупена от концерна Seagram, който е собственик и на най-голямата от малките звукозаписни компании тогава - Universal Music, или както я наричат „шестия мейджър”. Така се ражда Universal Music Group, под чиято шапка се събират значителна част от най-успешните музикални лейбъли в света. Вирджиния Рекърдс е изключителен лицензиант на Universal Music в България за период от 18 години и партньорството продължава в областта на разпространението на физически носители.', '$2y$10$qSodtWKkCydRRjlV3o9FlOL5kfnWH4yytTpWX.w7SfDqljGxNiWtm', '/uploads/profile-pictures/10.jpg', '{\"currency\":\"3\",\"email\":{\"user\":{\"hostChanged\":\"1\",\"hostDeleted\":\"1\",\"venueSet\":\"1\",\"eventUpdatedHost\":\"1\",\"venueDeleted\":\"1\",\"eventDeleted\":\"1\",\"ownerApproved\":\"1\",\"ownerRejected\":\"1\",\"artistApproved\":\"1\",\"artistRejected\":\"1\"},\"owner\":{\"venueDeleted\":\"0\",\"eventDeleted\":\"0\",\"artistApproved\":\"0\",\"artistRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"eventAddRequested\":\"0\"},\"host\":{\"venueSet\":\"1\",\"eventDeleted\":\"1\",\"venueDeleted\":\"1\",\"ownerDeleted\":\"1\",\"ownerSet\":\"1\",\"ownerApproved\":\"1\",\"ownerRejected\":\"1\",\"artistApproved\":\"1\",\"artistRejected\":\"1\",\"artistDeleted\":\"1\",\"eventAdded\":\"1\",\"eventUpdatedAdmin\":\"1\"},\"artist\":{\"ownerSet\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"hostChanged\":\"0\",\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"artistPending\":\"0\"}}}', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-21 18:13:58', '2022-12-21 18:22:13'),
(11, 'artist1', 'dara.ekimova@test.bg', 'Дара', 'Екимова', 1, '479256876', '123455432112345', 1, 'бл.29, ж.к. Разсадника-Коньовица', 'Дара Екимова е българска певица. Участник в телевизионното шоу „Големите надежди“, както и в „Екс фактор“, където не е допусната да продължи поради ниската си по това време възраст спрямо регламента.[1] По-значим неин запис е ремиксът на песента „Синьо“, изпята в оригинал от баща ѝ Дими – главен вокалист на група „Сленг“.', '$2y$10$0IeKkbmdYNUVyQJvmF/hsu/lbHWdcQAwNwEqCJGZKhU4ow7kJZy1e', '/uploads/profile-pictures/11.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 15:43:45', '2022-12-22 15:48:08'),
(12, 'artist2', 'darnadude@gmail.com', 'Дарина', 'Йотова', 1, '859035008', '1234509876378043', 1, 'ж.к. Лозенец, Sofia, 45, Yoan Ekzarh', 'Дарина Николаева Йотова, по-известна като Дара, е българска певица. Добива популярност в края на 2015 г. с участието си в телевизионното състезание „X Factor“.', '$2y$10$B8ueVZT1zRmkIuFELzHIuuY5Af0O6Fpcg8uX8ktN1s6VifLil87Bm', '/uploads/profile-pictures/12.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 15:50:05', '2022-12-22 15:51:20'),
(13, 'artist3', 'grafa@montemusic.bg', 'Владимир', 'Ампов', 1, '567289456', '1234590475865367', 1, 'zh.k. Krasna polyana 2,  бл.211', 'Владимир Кирилов Ампов (роден на 21 юли 1978), познат с артистичния си псевдоним Графа, е известен български поп певец, композитор и музикален продуцент. Син е на музикантите Тони Ампова и Кирил Ампов[2], и двамата част от трио „Спешен случай“.', '$2y$10$.pZrIY2iRku.h5.6PVTNL.4UGllIIhjOZRziIqmlilL2lduDXQJka', '/uploads/profile-pictures/13.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 15:52:34', '2022-12-22 15:53:26'),
(14, 'artist4', 'lyubo@abv.bg', 'Любо', 'Киров', 1, '489268478', '123456789408525', 1, 'zh.k. Lyulin,  29, bul. Dzhavaharlal Neru', 'Lubomir Tsvetanov Kirov (Bulgarian: Любо Киров) is a Bulgarian pop singer and music producer. He was formerly the lead singer of the band Te and was a member of the jury in the second and third season of music show X Factor.', '$2y$10$IgTwtOP41TJVtri3E3vstuKclXbH26BoTtKMMGx1UY2Z8xwIaYwr2', '/uploads/profile-pictures/14.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 15:54:43', '2022-12-22 15:55:16'),
(15, 'artist5', 'm.fileva@montemusic.bg', 'Михаела', 'Филева', 1, '379276489', '478936567812345', 1, 'улица Акация 3, Хасково, 6304   Хасково,България', 'Михаела Тинкова Филева е българска певица, авторка на текстове и песни, озвучаваща актриса и активистка за правата на ЛГБТ хората.\n\nКариерата ѝ започва през 2005 г., след като участва в първото телевизионно музикално риалити в България „Хит минус едно“, откъдето набира популярност. Възпитаничка е на естрадно студио „Румина“, чрез което е участвала в редица конкурси и фестивали в Молдова, Франция, Италия, Полша. Следват редица фестивали в България през 2002, 2003 до 2009 г.\n\nСлед това през 2011 участва в първото издание на „X Factor“. Още на следващата година става първия топ артист на музикалния издател „Монте Мюзик“.', '$2y$10$cJXhASqSGecpkY8xODXIr.0i4jky.SvhiZ/5G5aXeNTazuAQ17C3i', '/uploads/profile-pictures/15.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 15:57:19', '2022-12-22 15:58:01'),
(16, 'artist6', 'mmlove@virginiarecords.com', 'Михаела', 'Маринова', 1, '590456932', '568925765234654', 1, 'улица Петър Стоянов 35, Благоевград, 2700   Благоевград,България', 'Mihaela Marinova (Bulgarian: Михаела Маринова; born 7 May 1998) is a Bulgarian singer - Mihaela Marinova is a multiple-time award winner and X Factor Bulgaria 2017 finalist. She is also part of the country’s biggest record label – Virginia Records. Having performed with her at the X Factor finale, British superstar James Arthur shares: “When I first saw Mihaela, I thought she had an incredible talent. She’s got all it takes to become a world success.” Her groundbreaking singles and videos have gathered more than 60M views and streams. Each of the tracks have been at leading positions in the Official Airplay Chart, with her smashing debut “Stapka Napred” (2015) having spent 5 consecutive weeks at #1. The following year (2016) the song brought Mihaela the prestigious awards “Song of the Year”, “Best Debut” and “Video of the Year” at the Bulgarian Annual Music Awards.', '$2y$10$a0huLhRNbqZDv/JunkJY8OtlwxUBcXhRMKrwRxg1hf9yTYgqkXAtW', '/uploads/profile-pictures/16.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 15:59:33', '2022-12-22 16:00:19'),
(17, 'artist7', 'tino@gmail.com', 'Кирил', 'Хаджиев', 1, '567098123', '6785624356098765', 1, 'улица Люлин 22, Сливен, 8800   Сливен,България', 'Кирил Хаджиев-Тино е сред финалистите от последния сезон на „Гласът на България”. Той е различният глас, който омагьоса публиката с невероятните низини в тембъра си и с магнетичното си присъствие на сцената. Още в самото начало той изненада публиката, като избра Камелия за свой треньор. Сега, след като шоуто вече приключи, казва, че това е бил най-правилният избор. Тино няма музикално образование. Всичко, което постига, е благодарение на собственото си виждане за музиката.', '$2y$10$wMrHswQiUuRO7x7aMi2sru42zs0lBuweY5MK.ttvpD2bWFhvmkUqO', '/uploads/profile-pictures/17.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 16:05:08', '2022-12-22 16:05:57'),
(18, 'artist8', 'maria.ilieva@stereoroom.com', 'Мария', 'Илиева', 1, '6789123476', '1234567890467890', 1, 'улица Грамаге 16, Перник, 2308   Перник,България', 'Maria Ilieva (Bulgarian: Мария Илиева) is a Bulgarian singer, songwriter and producer. She is recognized as one of the most successful female vocal artists of the contemporary Bulgarian music scene. In the last over 20 years of active solo career, she has released over 30 hit singles, two award winning studio albums, a maxi single and a greatest hits album. She has received over 40 awards for music and style.', '$2y$10$fxcsjSqzdaGJup5fBpcgEu8.gzU23/nQR7XziWgMQJinvqLBHEqt2', '/uploads/profile-pictures/18.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 17:20:31', '2022-12-22 17:21:06'),
(20, 'user1', 'moni@topvine.co', 'Simeon', 'Mihaylov', 1, '888744925', '123456789076543', 1, 'zh.k. Mladost 1A, bl. 527, entr. 6, fl. 6, ap. 134', 'I love cars :)', '$2y$10$HdBAFJhX/LRnfJr1FELN1ODTyFCHhFTmAdwJu.ne4FbIT7rSjBfRC', '/uploads/profile-pictures/20.jpg', '{\"currency\":\"1\",\"email\":{\"user\":{\"hostChanged\":\"1\",\"hostDeleted\":\"1\",\"venueSet\":\"1\",\"eventUpdatedHost\":\"0\",\"venueDeleted\":\"1\",\"eventDeleted\":\"1\",\"ownerApproved\":\"1\",\"ownerRejected\":\"0\",\"artistApproved\":\"1\",\"artistRejected\":\"1\"},\"owner\":{\"venueDeleted\":\"0\",\"eventDeleted\":\"0\",\"artistApproved\":\"0\",\"artistRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"eventAddRequested\":\"0\"},\"host\":{\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"ownerSet\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"artistApproved\":\"0\",\"artistRejected\":\"0\",\"artistDeleted\":\"0\",\"eventAdded\":\"0\",\"eventUpdatedAdmin\":\"0\"},\"artist\":{\"ownerSet\":\"0\",\"venueDeleted\":\"0\",\"ownerDeleted\":\"0\",\"hostChanged\":\"0\",\"venueSet\":\"0\",\"eventDeleted\":\"0\",\"ownerApproved\":\"0\",\"ownerRejected\":\"0\",\"hostDeleted\":\"0\",\"hostSet\":\"0\",\"artistDeleted\":\"0\",\"artistPending\":\"0\"}}}', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-22 18:23:03', '2022-12-23 16:20:13'),
(21, 'user2', 'selena@rarebeauty.com', 'Надежда', 'Петрова', 1, '358047256', '1234565437809876', 1, 'улица Каменоломна 15, Габрово, 5301   Габрово,България', NULL, '$2y$10$q3Sg53bDNQ/7hc7q9PbZ/.hEYkwfvZypx4zL8ePaIBXJyjHzYaFjy', '/uploads/profile-pictures/21.jpg', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-23 16:21:45', '2022-12-23 16:22:06'),
(22, 'user3', 'selena.gomez.92@gmail.com', 'Katherine', 'McNamara', 2, '579267367', '568903682576546', 2, 'Baden-Württemberg, Stuttgart Feuerbach, Ollenhauer Str. 32', 'Love to listen to foreign music ;)', '$2y$10$Y/z0OJX3VVCEBm.HFc2EBujTwCgfRyASyCrITv0W051EyTWHkJHo2', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-23 16:24:32', '2022-12-23 16:25:05'),
(23, 'admin', 'admin@admin.bg', 'Петър', 'Иванов', 1, '678365089', '4780346542379510', 1, 'улица Цанко Церковски 10, Плевен, 5800   Плевен,България', 'Admin of Tickets Heaven website.', '$2y$10$pmsyuAQzsRN0XKN6wiSU9ey7TwLYPQSwr9kz3bxTpgeg0Dk/S.mx6', NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2022-12-25 08:56:46', '2022-12-25 08:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE `users_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `owner` tinyint(1) NOT NULL,
  `host` tinyint(1) NOT NULL,
  `artist` tinyint(1) NOT NULL,
  `user` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `users_permissions`
--

INSERT INTO `users_permissions` (`id`, `user_id`, `admin`, `owner`, `host`, `artist`, `user`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0, 0, 0, 1, '2022-12-21 15:50:46', '2022-12-21 15:50:46'),
(2, 2, 0, 1, 0, 0, 0, '2022-12-21 16:00:18', '2022-12-21 16:02:25'),
(3, 3, 0, 1, 0, 0, 0, '2022-12-21 16:05:06', '2022-12-21 16:05:56'),
(4, 4, 0, 1, 0, 0, 0, '2022-12-21 16:09:12', '2022-12-21 16:11:06'),
(5, 5, 0, 1, 0, 0, 0, '2022-12-21 16:14:20', '2022-12-21 16:14:37'),
(6, 6, 0, 1, 0, 0, 0, '2022-12-21 16:17:10', '2022-12-21 16:18:06'),
(7, 7, 0, 0, 0, 0, 1, '2022-12-21 16:50:30', '2022-12-21 16:50:30'),
(8, 8, 0, 0, 1, 0, 0, '2022-12-21 18:02:54', '2022-12-21 18:03:08'),
(9, 9, 0, 0, 1, 0, 0, '2022-12-21 18:10:39', '2022-12-21 18:14:17'),
(10, 10, 0, 0, 1, 0, 0, '2022-12-21 18:13:58', '2022-12-21 18:19:15'),
(11, 11, 0, 0, 0, 1, 0, '2022-12-22 15:43:45', '2022-12-22 15:44:58'),
(12, 12, 0, 0, 0, 1, 0, '2022-12-22 15:50:05', '2022-12-22 15:51:02'),
(13, 13, 0, 0, 0, 1, 0, '2022-12-22 15:52:35', '2022-12-22 15:52:48'),
(14, 14, 0, 0, 0, 1, 0, '2022-12-22 15:54:43', '2022-12-22 15:54:56'),
(15, 15, 0, 0, 0, 1, 0, '2022-12-22 15:57:19', '2022-12-22 15:57:59'),
(16, 16, 0, 0, 0, 1, 0, '2022-12-22 15:59:33', '2022-12-22 16:00:16'),
(17, 17, 0, 0, 0, 1, 0, '2022-12-22 16:05:08', '2022-12-22 16:05:52'),
(18, 18, 0, 0, 0, 1, 0, '2022-12-22 17:20:31', '2022-12-22 17:20:46'),
(19, 19, 0, 0, 0, 0, 1, '2022-12-22 18:22:00', '2022-12-22 18:22:00'),
(20, 20, 0, 0, 0, 0, 1, '2022-12-22 18:23:03', '2022-12-22 18:23:03'),
(21, 21, 0, 0, 0, 0, 1, '2022-12-23 16:21:45', '2022-12-23 16:21:45'),
(22, 22, 0, 0, 0, 0, 1, '2022-12-23 16:24:32', '2022-12-23 16:24:32'),
(23, 23, 1, 0, 0, 0, 0, '2022-12-25 08:56:46', '2022-12-25 08:57:02');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `phone_code_id` int(11) NOT NULL,
  `phone_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `opens` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `closes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `venue_picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `description`, `address`, `phone_code_id`, `phone_number`, `opens`, `closes`, `owner_id`, `venue_picture`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Бар Петък', 'Every day is FRIDAY here, and everyone who has ever set foot in it knows that. DJ events, acoustic concerts, exhibitions, film screenings, literary readings, bazaars, lectures and all kinds of other Friday magic await you from Monday to Sunday at your favorite bar of the week!', 'ul. Gen. Y. V. Gurko 21, Sofia, Bulgaria', 1, '893624062', '20:00', '06:00', 2, '/uploads/venue-pictures/1.jpg', '2022-12-21 16:19:24', '2022-12-21 16:19:24', NULL),
(4, 'Club Terminal 1', 'Club Terminal 1 is situated in very city center of Sofia (1 Angel Kanchev str.) and has unique interior inspired by the pop and alternative music that are mostly played in it. The place is suitable for live performances with big mobile stage, two floors and a total capacity of 700 people. The three large bars (plus one dedicated Shot Bar) with English speaking bartenders offer the clients nice experience and quality service. On the first floor are situated foosball tables and electronic games. The wardrobe is on the second floor - facing the exit. Both floors of the club have their own restrooms.', 'ul. \"Angel Kanchev\" 1, 1000 Sofia Center, Sofia', 1, '889219001', '21:00', '07:00', 3, '/uploads/venue-pictures/4.jpg', '2022-12-21 16:23:26', '2022-12-21 16:37:14', NULL),
(5, 'Joy Station', 'Joy Station is an ultra-modern party center that brings together the largest bowling alley in Bulgaria and a club whose technical parameters and size of the stage are a compliment to every global star. The site is on 2 levels and has an area of over 5,000 square meters. The bowling alley features 20 modern tracks with American equipment, eye-catching lighting, and attracts athletes from all over the world. At Joy Station you can play billiards, foosball, have fun with various video games and end your day with an electrifying concert, stand-up or theatre. Level 1, where the club is, has a spacious terrace and a garden with a playground for the smallest customers.', ' ulitsa \"Akademik Stefan Mladenov\" 3, 1700 Studentski grad, Sofia', 1, '894777251', '19:00', '00:00', 4, '/uploads/venue-pictures/5.jpg', '2022-12-21 16:25:03', '2022-12-21 16:37:41', NULL),
(6, 'NDK', 'The National Palace of Culture (Национален дворец на културата, Natsionalen dvorets na kulturata; abbreviated as НДК, NDK), located in Sofia, the capital of Bulgaria, is the largest, multifunctional conference and exhibition centre in south-eastern Europe. It was opened in 1981 in celebration of Bulgaria\'s 1300th anniversary.', 'Bulevard \"Bulgaria\", 1463 Ndk, Sofia', 1, '889050566', '10:00', '23:00', 6, '/uploads/venue-pictures/6.jpg', '2022-12-21 16:26:37', '2022-12-24 20:19:12', NULL),
(7, 'Sofia Live Club', 'Най-големият клуб за жива музика в София, който отговаря изцяло на високите стандарти от европейската и световна клубна сцена.', 'Bulevard \"Bulgaria\" 1, 1000 Ndk, Sofia', 1, '886660720', '19:00', '05:00', 5, '/uploads/venue-pictures/7.jpg', '2022-12-21 16:27:45', '2022-12-24 20:19:01', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `continents`
--
ALTER TABLE `continents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_participants`
--
ALTER TABLE `event_participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phone_codes`
--
ALTER TABLE `phone_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_permissions`
--
ALTER TABLE `users_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `continents`
--
ALTER TABLE `continents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `event_participants`
--
ALTER TABLE `event_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `phone_codes`
--
ALTER TABLE `phone_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users_permissions`
--
ALTER TABLE `users_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
