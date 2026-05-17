-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 08:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_order_delivery`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chef_profiles`
--

CREATE TABLE `chef_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `bio` text DEFAULT NULL,
  `heritage_story` text DEFAULT NULL,
  `years_experience` varchar(255) DEFAULT NULL,
  `cuisine_type` varchar(255) DEFAULT NULL,
  `specialties` varchar(255) DEFAULT NULL,
  `specialties_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specialties_list`)),
  `kitchen_address` varchar(255) DEFAULT NULL,
  `food_handler_certificate_no` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chef_profiles`
--

INSERT INTO `chef_profiles` (`id`, `user_id`, `bio`, `heritage_story`, `years_experience`, `cuisine_type`, `specialties`, `specialties_list`, `kitchen_address`, `food_handler_certificate_no`, `created_at`, `updated_at`) VALUES
(1, 3, 'Elena grew up in a small coastal town in Spain, where she learned traditional Mediterranean cooking from her grandmother. Her dishes celebrate fresh seafood, olive oil, and seasonal vegetables with vibrant flavors that transport you to the Mediterranean coast.', 'Born in a small coastal town in Spain, Elena learned traditional Mediterranean cooking from her grandmother. Her dishes celebrate fresh seafood, olive oil, and seasonal vegetables.', '12', 'Mediterranean', 'Mediterranean Master, Seafood Expert, Organic Specialist', '[\"Mediterranean Master\",\"Seafood Expert\",\"Organic Specialist\"]', 'Coastal Kitchen, Masaki', 'FH-ELE8213', '2026-01-26 19:41:53', '2026-01-26 19:41:53'),
(2, 4, 'Sarah combines traditional Asian techniques with modern presentation. Her innovative approach to fusion cuisine has earned her recognition in top culinary magazines. She spent 8 years studying culinary arts across Asia, mastering techniques from Japan, Korea, Thailand, and Vietnam.', 'Sarah spent 8 years studying culinary arts across Asia, mastering techniques from Japan, Korea, Thailand, and Vietnam. This fusion masterpiece was born from her childhood split between Seoul and Los Angeles.', '12', 'Asian Fusion', 'Fusion Expert, James Beard Nominee, Sustainable Cooking, Innovation Award', '[\"Fusion Expert\",\"James Beard Nominee\",\"Sustainable Cooking\",\"Innovation Award\"]', 'East Side Bistro, Oysterbay', 'FH-SAR8721', '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(3, 5, 'Trained in Lyon and Paris under Michelin-starred chefs, Antoine brings classical French techniques to modern dining. His passion for French gastronomy is evident in every meticulously crafted dish, from delicate soufflés to rich coq au vin.', 'Trained in Lyon and Paris under Michelin-starred chefs, Antoine brings classical French techniques to modern dining.', '15', 'French Cuisine', 'Michelin Trained, French Master, Classical Techniques', '[\"Michelin Trained\",\"French Master\",\"Classical Techniques\"]', 'Le Petit Bistro, Mikocheni', 'FH-ANT6957', '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(4, 6, 'With over 15 years of experience in authentic Italian cooking, Marco brings the flavors of Tuscany to your table. Trained in Milan and Rome, he specializes in handmade pasta and traditional sauces using recipes passed down through generations.', 'Born in the hills of Umbria, this carbonara recipe was passed down through four generations. Marco learned from his nonna who learned from her nonna, preserving the authentic flavors of Italian tradition.', '15', 'Italian Cuisine', 'Michelin Trained, Pasta Expert, 15+ Years Experience', '[\"Michelin Trained\",\"Pasta Expert\",\"15+ Years Experience\"]', 'Downtown Kitchen, Upanga', 'FH-MAR4760', '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(5, 7, 'Master of the grill with a passion for slow-cooked meats and homemade sauces. James brings authentic Southern BBQ traditions with his own modern twist. His 20-year journey started in Texas pit houses and led him to compete in national BBQ championships.', 'James discovered this ranch during his apprenticeship in Kobe, where he learned the art of premium beef preparation from master butchers.', '20', 'American BBQ', 'BBQ Master, Sauce Specialist, National Champion, 20+ Years Experience', '[\"BBQ Master\",\"Sauce Specialist\",\"National Champion\",\"20+ Years Experience\"]', 'Smokehouse Central, Kariakoo', 'FH-JAM5887', '2026-01-26 19:41:57', '2026-01-26 19:41:57');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `traveler_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'unassigned',
  `traveler_earning` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `traveler_id`, `status`, `traveler_earning`, `created_at`, `updated_at`) VALUES
(1, 1, 28, 'delivered', 8.80, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(2, 3, 29, 'assigned', 0.00, '2026-01-29 07:00:14', '2026-01-29 07:00:14');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT 'Tanzania',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `user_id`, `label`, `address_line`, `city`, `region`, `country`, `latitude`, `longitude`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 3, 'Masaki', 'Coastal Kitchen, Masaki', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7910000, 39.2900000, 1, '2026-01-26 19:41:53', '2026-01-26 19:41:53'),
(2, 4, 'Oysterbay', 'East Side Bistro, Oysterbay', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7930000, 39.2910000, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(3, 5, 'Mikocheni', 'Le Petit Bistro, Mikocheni', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7250000, 39.2020000, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(4, 6, 'Upanga', 'Downtown Kitchen, Upanga', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7520000, 39.2280000, 1, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(5, 7, 'Kariakoo', 'Smokehouse Central, Kariakoo', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7800000, 39.2390000, 1, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(6, 8, 'City Center', '68015 Carolanne Crescent Suite 158', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7001820, 39.2551790, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(7, 9, 'City Center', '8799 Bednar Mountain', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7387910, 39.2752370, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(8, 10, 'Upanga', '668 Reinger Oval Apt. 269', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7425660, 39.2438970, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(9, 11, 'City Center', '912 Hertha Crest', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7589680, 39.2111910, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(10, 12, 'Upanga', '544 Welch Divide Suite 325', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7522000, 39.2895880, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(11, 13, 'Mikocheni', '77996 Harber Inlet', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7569130, 39.2588770, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(12, 14, 'Kariakoo', '6576 Loren Port Apt. 180', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7443640, 39.2083270, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(13, 15, 'Mikocheni', '93162 Sawayn Brooks Suite 079', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7655560, 39.2701390, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(14, 16, 'Masaki', '4257 Regan Greens', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7129000, 39.2336280, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(15, 17, 'Upanga', '331 Saul Mill', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7813450, 39.2694270, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(16, 18, 'Mikocheni', '112 Reymundo Fort', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7877980, 39.2931570, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(17, 19, 'Mikocheni', '892 Brown Coves Apt. 072', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7467650, 39.2613430, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(18, 20, 'Oysterbay', '227 Johnston Hills Suite 745', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7575280, 39.2473770, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(19, 21, 'Oysterbay', '902 Kathryn Rapids', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7446490, 39.2708480, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(20, 22, 'City Center', '355 Jadyn Pass', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7945630, 39.2956560, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(21, 23, 'Oysterbay', '3608 Ida Fields Apt. 512', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7399330, 39.2163860, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(22, 24, 'Oysterbay', '432 Jeffrey Hill Apt. 160', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7648990, 39.2911050, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(23, 25, 'Kariakoo', '3781 Rowe Stravenue', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7848230, 39.2965790, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(24, 26, 'Kariakoo', '446 Jodie Glen', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7535210, 39.2301030, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(25, 27, 'Kariakoo', '4440 Zboncak Field Suite 436', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7024150, 39.2048380, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(26, 28, 'Oysterbay', '90864 Jessy Junctions Apt. 136', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7641810, 39.2027110, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(27, 29, 'Mikocheni', '38840 Buddy Center', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7042270, 39.2281020, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(28, 30, 'Mikocheni', '951 Domenica Road Suite 029', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7910480, 39.2509690, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(29, 31, 'Oysterbay', '6448 Wisozk Points Suite 738', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7046910, 39.2501330, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(30, 32, 'Kariakoo', '478 Batz Key Apt. 141', 'Dar es Salaam', 'Dar es Salaam', 'Tanzania', -6.7738710, 39.2069160, 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(31, 1, 'home', 'Rau\r\nMoshi', 'Kilimanjaro', 'moshi', 'Tanzania', NULL, NULL, 1, '2026-01-28 14:35:26', '2026-01-28 14:35:26'),
(32, 36, 'home', 'Rau\r\nMoshi', 'Kilimanjaro', 'moshi', 'Tanzania', NULL, NULL, 1, '2026-02-03 11:16:51', '2026-02-03 11:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `chef_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `heritage_story` text DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `prep_time_minutes` int(10) UNSIGNED DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `dietary_tags` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_heritage` tinyint(1) NOT NULL DEFAULT 0,
  `is_popular` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`id`, `chef_id`, `name`, `description`, `heritage_story`, `origin`, `prep_time_minutes`, `price`, `category`, `dietary_tags`, `image_path`, `is_available`, `is_heritage`, `is_popular`, `created_at`, `updated_at`) VALUES
(1, 3, 'Paella Valenciana', 'Traditional Spanish paella with saffron rice, seafood, and vegetables', NULL, NULL, 35, 32.00, 'Mediterranean', NULL, 'images/african food 02.jpg', 1, 1, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(2, 3, 'Grilled Octopus', 'Tender grilled octopus with olive oil, lemon, and herbs', NULL, NULL, 34, 28.00, 'Mediterranean', NULL, 'images/african food 01.jpg', 1, 0, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(3, 4, 'Korean BBQ Tacos', 'Marinated bulgogi beef in soft tortillas with kimchi slaw and spicy mayo', '200-Year-Old Fermentation Pot', 'Seoul-LA Fusion Heritage', 34, 18.00, 'Asian Fusion', NULL, 'images/food 01.jpeg', 1, 1, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(4, 4, 'Miso Glazed Salmon', 'Fresh salmon glazed with miso and served with steamed rice', NULL, NULL, 21, 26.00, 'Asian Fusion', NULL, 'images/african food 01.jpg', 1, 0, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(5, 4, 'Fresh Sushi Platter', 'Assorted nigiri and maki rolls with fresh wasabi and pickled ginger', NULL, 'Tokyo Tsukiji Tradition', 19, 42.00, 'Japanese', NULL, 'images/african food 01.jpg', 1, 0, 0, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(6, 5, 'Coq au Vin', 'Classic French chicken braised in wine with mushrooms and onions', NULL, NULL, 16, 34.00, 'French', NULL, 'images/food 01.jpeg', 1, 1, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(7, 5, 'Beef Bourguignon', 'Slow-cooked beef in red wine with vegetables and herbs', NULL, NULL, 24, 38.00, 'French', NULL, 'images/african food 01.jpg', 1, 1, 1, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(8, 6, 'Truffle Carbonara', 'Handmade pasta with pancetta, egg yolk, parmesan, and black truffle shavings', '4th Generation Recipe', 'Umbrian Family Tradition', 30, 28.00, 'Italian', NULL, 'images/african food 01.jpg', 1, 1, 1, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(9, 6, 'Artisan Chocolate Cake', 'Rich dark chocolate cake with ganache frosting and gold leaf garnish', 'Nonna\'s Legacy Recipe', 'Turin Chocolate Heritage', 17, 16.00, 'Desserts', NULL, 'images/african food 02.jpg', 1, 1, 0, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(10, 7, 'Premium Wagyu Steak', 'Grade A5 Wagyu beef with roasted vegetables and red wine reduction', 'Kobe Apprenticeship Legacy', 'Hyogo Prefecture, Japan', 44, 65.00, 'BBQ', NULL, 'images/food 01.jpeg', 1, 1, 1, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(11, 7, 'Smoked Brisket Platter', '12-hour smoked brisket with house-made BBQ sauce and pickles', NULL, 'Texas Cattle Drive Tradition', 45, 24.00, 'BBQ', NULL, 'images/african food 02.jpg', 1, 0, 1, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(12, 4, 'Leo Godwine', 'uygyiu', 'jhv.lcurltgj', 'hdfly', 23, 56.00, 'ghfli', 'iuguooi', 'meals/9QZfIdcCACB6R9SsbXOkcuM3sLBbzR0eSEnmz4YV.jpg', 1, 0, 0, '2026-01-29 12:32:08', '2026-01-29 12:32:08');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_26_000100_add_role_status_phone_to_users_table', 1),
(5, '2026_01_26_000200_create_locations_table', 1),
(6, '2026_01_26_000300_create_profiles_and_documents_tables', 1),
(7, '2026_01_26_000400_create_meals_orders_payments_deliveries_reviews_tables', 1),
(8, '2026_01_26_000500_add_heritage_stories_to_meals_and_chefs', 1),
(9, '2026_01_27_000001_create_order_chefs_table', 2),
(10, '2026_01_27_000002_make_orders_chef_id_nullable', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `chef_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `special_instructions` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `chef_id`, `status`, `special_instructions`, `subtotal`, `delivery_fee`, `total`, `delivery_location_id`, `created_at`, `updated_at`) VALUES
(1, 11, 3, 'delivered', 'Consequatur nihil sit fugiat est consectetur dicta qui.', 28.00, 11.00, 39.00, 9, '2026-01-19 19:41:58', '2026-01-26 19:41:58'),
(2, 16, 3, 'delivered', NULL, 0.00, 9.00, 0.00, 14, '2025-12-30 19:41:58', '2026-01-26 19:41:58'),
(3, 1, 3, 'pending', NULL, 60.00, 0.00, 60.00, 31, '2026-01-29 07:00:14', '2026-01-29 07:00:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_chefs`
--

CREATE TABLE `order_chefs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `chef_id` bigint(20) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `meal_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `line_total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `meal_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 28.00, 28.00, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(2, 3, 2, 1, 28.00, 28.00, '2026-01-29 07:00:14', '2026-01-29 07:00:14'),
(3, 3, 1, 1, 32.00, 32.00, '2026-01-29 07:00:14', '2026-01-29 07:00:14');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `provider_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `method`, `status`, `amount`, `provider_reference`, `created_at`, `updated_at`) VALUES
(1, 1, 'mpesa', 'paid', 39.00, 'REF-6977EDB63CE51', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(2, 3, 'mpesa', 'paid', 60.00, NULL, '2026-01-29 07:00:14', '2026-01-29 07:00:14');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `chef_id` bigint(20) UNSIGNED DEFAULT NULL,
  `traveler_id` bigint(20) UNSIGNED DEFAULT NULL,
  `chef_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `traveler_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('BTu92b3URgqRdor3iGDku7ILtPrR4A6UMdJSooJy', 37, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZ2w2UERZelphZWJYVW9waWpHYjBJVlZBb3RVMUtKOTd1c2hjSXBrWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jaGVmcyI7czo1OiJyb3V0ZSI7czoxMToiY2hlZnMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozNzt9', 1770186703),
('C2gckrtqfyBSnpnn9G6Txvz4eNPTd3LuYn2Xy4ly', 36, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6Iko0WlNWNUt6VlJLdzlOSkFCRWVaMGN3Mmg4a0kwOHh4eVJwa1p0Y1EiO3M6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvY2hlY2tvdXQ/c3RlcD01IjtzOjU6InJvdXRlIjtzOjE1OiJvcmRlcnMuY2hlY2tvdXQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozNjtzOjQ6ImNhcnQiO2E6Mjp7aToxO2k6MTtpOjI7aToxO31zOjI5OiJjaGVja291dF9kZWxpdmVyeV9sb2NhdGlvbl9pZCI7aTozMjtzOjIzOiJjaGVja291dF9wYXltZW50X21ldGhvZCI7czo2OiJhaXJ0ZWwiO3M6MjI6ImNoZWNrb3V0X3BheW1lbnRfcGhvbmUiO047czoyNjoiY2hlY2tvdXRfcGF5bWVudF9yZWZlcmVuY2UiO047czoyOToiY2hlY2tvdXRfc3BlY2lhbF9pbnN0cnVjdGlvbnMiO047fQ==', 1770128396);

-- --------------------------------------------------------

--
-- Table structure for table `traveler_profiles`
--

CREATE TABLE `traveler_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `vehicle_registration_no` varchar(255) DEFAULT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `traveler_profiles`
--

INSERT INTO `traveler_profiles` (`id`, `user_id`, `vehicle_type`, `vehicle_registration_no`, `is_online`, `created_at`, `updated_at`) VALUES
(1, 28, 'bicycle', 'TZ-9885720', 0, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(2, 29, 'car', 'TZ-0195162', 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(3, 30, 'car', 'TZ-2501002', 0, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(4, 31, 'bicycle', 'TZ-1476337', 1, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(5, 32, 'bicycle', 'TZ-9568887', 0, '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(6, 34, NULL, NULL, 0, '2026-02-03 10:57:52', '2026-02-03 10:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `status` varchar(255) NOT NULL DEFAULT 'approved',
  `approved_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `status`, `approved_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@fooddelivery.com', NULL, NULL, '$2y$12$Iv3IjMinj8NN0wJyrqoCF.QjiTSAkjFmtMUlU84zcu4nTnYaKoPPW', 'admin', 'approved', '2026-01-26 19:41:52', NULL, '2026-01-26 19:41:52', '2026-01-26 19:41:52'),
(2, 'Admin User', 'admin@onestop.com', NULL, NULL, '$2y$12$ZzQcWjhSEGiHBpryaty3GeSKGAMImzVWvyCJgTu6O96Yw4mC3iisG', 'admin', 'approved', NULL, NULL, '2026-01-26 19:41:52', '2026-01-26 19:41:52'),
(3, 'Elena Rodriguez', 'elena@onestop.com', '+255 626 377386', NULL, '$2y$12$4jZIP9Vy5xQprlZ4tGhfDePmhy0o//zQBOVjM0CsBRWBkDY.9zwNW', 'chef', 'approved', NULL, NULL, '2026-01-26 19:41:53', '2026-01-26 19:41:53'),
(4, 'Sarah Chen', 'sarah@onestop.com', '+255 626 584888', NULL, '$2y$12$NziyzWxes0URkOT.UaAQj.2NM52JTdaxiGLM01OXXQB/Wz.rWJh2e', 'chef', 'approved', NULL, NULL, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(5, 'Antoine Dubois', 'antoine@onestop.com', '+255 626 420768', NULL, '$2y$12$RjqYYvQSHziseW7dWEp6XOFxpurbzegeX9TyGqTL76eCEW/nWC9VW', 'chef', 'approved', NULL, NULL, '2026-01-26 19:41:56', '2026-01-26 19:41:56'),
(6, 'Marco Rodriguez', 'marco@onestop.com', '+255 626 690501', NULL, '$2y$12$QkfmhB0VNYMKWMMdAb69d.4AvKPbmF.XobdllNINACRlMETCuRbPm', 'chef', 'approved', NULL, NULL, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(7, 'James Thompson', 'james@onestop.com', '+255 626 372685', NULL, '$2y$12$rndqQX5MYhtFe715fPVHqOu4Xq0AqWx4fJXqzr2oAIQMgAHKPYLcK', 'chef', 'approved', NULL, NULL, '2026-01-26 19:41:57', '2026-01-26 19:41:57'),
(8, 'Miss Katlyn Ankunding Jr.', 'abagail.huels@example.com', NULL, '2026-01-26 19:41:57', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'hqVXtzj6Yg', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(9, 'Lewis Collier', 'ziemann.craig@example.net', NULL, '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'Xx6a25uzje', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(10, 'Harrison Bradtke', 'hessel.nikolas@example.com', '731.307.7534', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'BKS2ir8q74', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(11, 'Sophie Schaden', 'xschuster@example.org', '216-686-7227', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'DWBtn23AJ0', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(12, 'Geovanni Mohr', 'laisha43@example.net', '+13512542772', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'cvPWUFDSZW', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(13, 'Vicky Stroman', 'newton.nienow@example.net', '+19402778330', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'P82b8QEuQ4', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(14, 'Dr. Arvilla Rippin II', 'mprosacco@example.com', '+1 (617) 938-6109', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'mhGVOFBGp2', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(15, 'Cathrine Hartmann II', 'ymurphy@example.org', '+1-734-859-2596', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'XXtDAcysUy', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(16, 'Jordon Heaney', 'isobel35@example.com', NULL, '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'xlYctb7cUb', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(17, 'Dr. Theresia Kemmer I', 'oroberts@example.org', '+16123383441', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'dGjIgkBt99', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(18, 'Coralie Predovic', 'caterina59@example.org', '1-415-509-6312', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, '5SZUWiqrHp', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(19, 'Paolo Hahn PhD', 'haylee.turner@example.net', '+1-307-932-4730', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'wuO3TtAFv9', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(20, 'Prof. Osvaldo Bartell', 'soreilly@example.com', '+1-507-503-2847', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'byefLDpFiD', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(21, 'Quincy Rogahn', 'faltenwerth@example.net', NULL, '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'Nuwu6kT0P8', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(22, 'Prof. Janiya Paucek', 'kub.izaiah@example.org', '531-806-6613', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'lHmpBZ2hYh', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(23, 'Prof. Mathew Stark', 'abshire.domenick@example.org', '938-330-8281', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'n9ebjXdStz', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(24, 'Kailey King', 'harvey.althea@example.org', NULL, '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, '21voKpNgbt', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(25, 'Marcel Turcotte', 'kuhlman.joannie@example.net', '+1 (680) 556-5472', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, '2xO8jskZDN', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(26, 'Mrs. Talia Witting I', 'herman.maxime@example.com', '+1.612.476.0866', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, 'j6FFk3qxE1', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(27, 'Kasandra Osinski I', 'cartwright.tyrell@example.org', '(341) 297-5980', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'customer', 'approved', NULL, '25VyESDkxH', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(28, 'Eryn Zieme Jr.', 'teagan.wolff@example.org', '+1 (586) 861-3265', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'traveler', 'approved', NULL, 'C3DQVIIebH', '2026-01-26 19:41:58', '2026-01-28 04:00:38'),
(29, 'Magnolia Mitchell', 'leannon.kelton@example.net', '(925) 529-8634', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'traveler', 'approved', NULL, '0OVbgpAlRL', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(30, 'Miss Elza Botsford', 'magdalen.greenholt@example.com', '+14197085766', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'traveler', 'approved', NULL, 'CA36E5Dqqo', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(31, 'Ebony Konopelski', 'xwaters@example.net', '559.619.4088', '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'traveler', 'approved', NULL, 'XjTaT87LYM', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(32, 'Mitchel Kirlin MD', 'hermiston.ned@example.net', NULL, '2026-01-26 19:41:58', '$2y$12$MyZvP9EqlWXPvDgvBEq39uCXQZbgODOoPg9Ffc86qmyPXauumzeCa', 'traveler', 'approved', NULL, 'IGp5aIiojV', '2026-01-26 19:41:58', '2026-01-26 19:41:58'),
(34, 'godwine leo', 'godwine@fooddelivery.com', '+255626725383', NULL, '$2y$12$s9hSU87Ro4lJPO1b4yZ06ehyFWzfEmVw1t/gxWFnBoXDT6aeKagoW', 'traveler', 'approved', NULL, NULL, '2026-01-30 04:12:21', '2026-01-30 05:59:09'),
(35, 'Godwine', 'godwine6@fooddelivery.com', NULL, NULL, '$2y$12$OxpFoyD68eIGCzq3BUnBk.E3GVrBWw3CGWb38wwV6q4OnwIkZQvrC', 'traveler', 'pending', NULL, NULL, '2026-01-30 15:45:21', '2026-01-30 15:45:21'),
(36, 'godwine leo', 'leo@fooddelivery.com', '+255626725386', NULL, '$2y$12$8IRrvi1D4bKOyFBA3rI3NOrBnlEFfcho0FY.CGi4qhAmBQb8Js0nm', 'traveler', 'approved', NULL, NULL, '2026-02-03 11:09:08', '2026-02-03 11:11:50'),
(37, 'kindago', 'kindago@gmail.com', '+255626725380', NULL, '$2y$12$QoOXdlFoywpH/uJA9GdzT.rDmJ3f829u.qSTzu6M4veXcTl6zl81.', 'customer', 'approved', NULL, NULL, '2026-02-04 03:27:19', '2026-02-04 03:27:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_verification_documents`
--

CREATE TABLE `user_verification_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `document_no` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `chef_profiles`
--
ALTER TABLE `chef_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chef_profiles_user_id_unique` (`user_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deliveries_order_id_unique` (`order_id`),
  ADD KEY `deliveries_traveler_id_foreign` (`traveler_id`),
  ADD KEY `deliveries_status_index` (`status`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `locations_user_id_is_primary_index` (`user_id`,`is_primary`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meals_chef_id_is_available_index` (`chef_id`,`is_available`),
  ADD KEY `meals_is_available_index` (`is_available`),
  ADD KEY `meals_is_heritage_index` (`is_heritage`),
  ADD KEY `meals_is_popular_index` (`is_popular`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_delivery_location_id_foreign` (`delivery_location_id`),
  ADD KEY `orders_customer_id_status_index` (`customer_id`,`status`),
  ADD KEY `orders_chef_id_status_index` (`chef_id`,`status`),
  ADD KEY `orders_status_index` (`status`);

--
-- Indexes for table `order_chefs`
--
ALTER TABLE `order_chefs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_chefs_order_id_chef_id_unique` (`order_id`,`chef_id`),
  ADD KEY `order_chefs_chef_id_status_index` (`chef_id`,`status`),
  ADD KEY `order_chefs_status_index` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_meal_id_foreign` (`meal_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_order_id_unique` (`order_id`),
  ADD KEY `payments_method_index` (`method`),
  ADD KEY `payments_status_index` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`),
  ADD KEY `reviews_customer_id_foreign` (`customer_id`),
  ADD KEY `reviews_chef_id_chef_rating_index` (`chef_id`,`chef_rating`),
  ADD KEY `reviews_traveler_id_traveler_rating_index` (`traveler_id`,`traveler_rating`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `traveler_profiles`
--
ALTER TABLE `traveler_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `traveler_profiles_user_id_unique` (`user_id`),
  ADD KEY `traveler_profiles_is_online_index` (`is_online`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_role_index` (`role`),
  ADD KEY `users_status_index` (`status`);

--
-- Indexes for table `user_verification_documents`
--
ALTER TABLE `user_verification_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_verification_documents_user_id_type_index` (`user_id`,`type`),
  ADD KEY `user_verification_documents_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chef_profiles`
--
ALTER TABLE `chef_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_chefs`
--
ALTER TABLE `order_chefs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `traveler_profiles`
--
ALTER TABLE `traveler_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `user_verification_documents`
--
ALTER TABLE `user_verification_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chef_profiles`
--
ALTER TABLE `chef_profiles`
  ADD CONSTRAINT `chef_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_traveler_id_foreign` FOREIGN KEY (`traveler_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_delivery_location_id_foreign` FOREIGN KEY (`delivery_location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_chefs`
--
ALTER TABLE `order_chefs`
  ADD CONSTRAINT `order_chefs_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_chefs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_meal_id_foreign` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`id`),
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_traveler_id_foreign` FOREIGN KEY (`traveler_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `traveler_profiles`
--
ALTER TABLE `traveler_profiles`
  ADD CONSTRAINT `traveler_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_verification_documents`
--
ALTER TABLE `user_verification_documents`
  ADD CONSTRAINT `user_verification_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
