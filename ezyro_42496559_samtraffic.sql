-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql313.ezyro.com
-- Generation Time: Jul 25, 2026 at 01:20 PM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ezyro_41469592_samtraffic`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'massoudisameh07@gmail.com', '$2y$10$sRsj7c2h0Zr73bqycXPF/u6XZsait4AEoV21zbvKeESz8QVV5yCBS', '2026-04-04 19:55:54');

-- --------------------------------------------------------

--
-- Table structure for table `ai_settings`
--

CREATE TABLE `ai_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `raw_code` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_settings`
--

INSERT INTO `ai_settings` (`id`, `setting_key`, `setting_value`, `raw_code`, `created_at`, `updated_at`) VALUES
(1, 'nvidia_config', '{\"provider\":\"opencode_zen\",\"api_key\":\"sk-pnQZaK7u1HxSOEN95vrRGXtTi2fcgXekWGOmhtF39RvrZTAKHu450m1qb2NG0pBY\",\"model\":\"minimax-m2.5-free\",\"temperature\":0.59999999999999997779553950749686919152736663818359375,\"top_p\":0.9499999999999999555910790149937383830547332763671875,\"max_tokens\":8192,\"seed\":null,\"extra_body\":null,\"has_reasoning\":false,\"reasoning_attr\":\"reasoning_content\",\"base_url\":\"https:\\/\\/opencode.ai\\/zen\\/v1\\/chat\\/completions\"}', 'from openai import OpenAI\nclient = OpenAI(\n    api_key=\"sk-pnQZaK7u1HxSOEN95vrRGXtTi2fcgXekWGOmhtF39RvrZTAKHu450m1qb2NG0pBY\",\n    base_url=\"https://opencode.ai/zen/v1/chat/completions\"\n)\nmodel=\"minimax-m2.5-free\"\ntemperature=0.6', '2026-04-28 18:39:57', '2026-04-28 19:25:03');

-- --------------------------------------------------------

--
-- Table structure for table `app_news_messages`
--

CREATE TABLE `app_news_messages` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `frequency` varchar(50) NOT NULL DEFAULT 'manual',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_updates`
--

CREATE TABLE `app_updates` (
  `id` int(11) NOT NULL,
  `version` varchar(50) NOT NULL,
  `release_notes` text DEFAULT NULL,
  `developer_message` text DEFAULT NULL,
  `update_news` text DEFAULT NULL,
  `download_mode` varchar(50) NOT NULL DEFAULT 'direct',
  `direct_url` text DEFAULT NULL,
  `external_url` text DEFAULT NULL,
  `uploaded_file_path` text DEFAULT NULL,
  `uploaded_file_name` varchar(255) DEFAULT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `force_update` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_updates`
--

INSERT INTO `app_updates` (`id`, `version`, `release_notes`, `developer_message`, `update_news`, `download_mode`, `direct_url`, `external_url`, `uploaded_file_path`, `uploaded_file_name`, `file_size`, `force_update`, `is_active`, `created_at`) VALUES
(1, '1.2.3', '', '', '', 'external', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.3.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.3.exe', '', '', NULL, 0, 0, '2026-04-05 01:42:03'),
(2, '1.2.3.0', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.3.0.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.3.0.exe', '', '', NULL, 1, 0, '2026-04-05 18:58:20'),
(3, '1.2.3.0.0', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup.exe', '', '', NULL, 0, 0, '2026-04-06 21:57:54'),
(4, '1.2.4', 'Today\'s New Features (v1.2.4)\r\n1. Search Engine Visit Fix\r\nFixed the search engine visit feature - the program now properly opens the search engine (Google/Bing/etc) before searching for the target URL\r\n2. Removed Ad Clicks from Live Monitor\r\nRemoved the \"Ad clicks\" option from the live monitoring section (was non-functional)\r\n3. Exit IP Display\r\nAdded \"Exit IP\" field to live sessions monitor showing the real IP address that the proxy connection exits from\r\nShows proxy IP (without port) separately from the actual exit IP\r\n4. Help Tooltips Improvement\r\nMoved the help icon (?) next to the feature name instead of next to the toggle button\r\nImproved tooltip visibility with:\r\nDark background with purple border\r\nWhite text for better readability\r\nWorks on hover and click\r\nProper z-index to appear above other elements\r\n5. Auto-scroll Ad Detection\r\nEnhanced scroll feature to automatically detect ads while scrolling\r\nWhen an ad is detected, the program pauses for 3-5 seconds before continuing\r\nDetects multiple ad types: Google Ads, ad containers, sponsored content, ad slots, etc.\r\n6. Version Update\r\nUpdated program version to 1.2.4', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.4.exe', '', '', NULL, 0, 0, '2026-04-07 04:46:29'),
(5, '1.2.5', '', 'English: SAM Traffic Pro v1.2.5 is now available! This update includes: a welcome screen with program logo and 5-second timer, new device distribution system (Mobile 40%, Desktop 30%, Laptop 20%, Tablet 10%), sitemap URL fetching with manual input support, automatic cookie consent clicking for GDPR popups, notification popup for updates (appears only when new version is available), renamed \"Settings\" to \"Devices Settings\", and removed unnecessary download mode button. Download now!\r\n\r\nالعربية: يتوفر الآن SAM Traffic Pro الإصدار 1.2.5! يتضمن هذا التحديث: شاشة ترحيب مع شعار البرنامج ومؤقت 5 ثواني، نظام توزيع الأجهزة الجديد (هاتف 40%، كمبيوتر 30%، لابتوب 20%، تابليت 10%)، جلب الروابط من خريطة الموقع مع دعم الإدخال اليدوي، الضغط التلقائي على موافقات ملفات تعريف الارتباط، نافذة إشعار بالتحديثات (تظهر فقط عند توفر تحديث جديد)، إعادة تسمية \"الإعدادات\" إلى \"إعدادات الأجهزة\"، وإزالة زر وضع التحميل الغير ضروري. حمل الآن!\r\n\r\nFrançais: SAM Traffic Pro v1.2.5 est maintenant disponible! Cette mise à jour incluye: écran de bienvenida avec logo du programme et minuteur de 5 secondes, nouveau système de distribution des appareils (Mobile 40%, Ordinateur 30%, Ordinateur portable 20%, Tablette 10%), récupération des URLs du sitemap avec support de saisie manuelle, clic automatique sur les consentements cookies, fenêtre de notification pour les mises à jour (apparaît uniquement quand une nouvelle version est disponible), renommage de \"Paramètres\" en \"Paramètres des appareils\", et suppression du bouton mode de téléchargement inutile. Téléchargez maintenant!', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.5.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.2.5.exe', '', '', NULL, 1, 0, '2026-04-08 19:12:54'),
(6, '1.2.5', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-1.2.5.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-1.2.5.exe', '', '', NULL, 0, 0, '2026-04-09 20:51:53'),
(7, '1.3', 'SAM Traffic Pro v1.3.0 - Update Released\r\nWhat\'s New:\r\n\r\nRemoved CPU usage display - now shows RAM only in System Resources\r\nAdded Device ID to live session info\r\nRedesigned monitor layout - all tools organized in equal grid with no empty spaces\r\nAdded device info section: Screen Resolution, CPU Cores, Device RAM, Color Depth\r\nBug Fixes:\r\n\r\nFixed system info display compatibility\r\nImproved session information display', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.0.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.0.exe', '', '', NULL, 0, 0, '2026-04-10 19:14:48'),
(8, '1.3.1', '- Search engine traffic fixes\r\n- Additional search engine traffic sources added\r\n- Incognito mode improved\r\n- Program design improved\r\n- Browsing in traffic is improved to simulate real-world traffic\r\n- Program speed and other background features improved', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.1.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.1.exe', '', '', NULL, 0, 0, '2026-04-12 02:04:31'),
(9, '1.3.2', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.2.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.2.exe', '', '', NULL, 1, 0, '2026-04-13 18:08:51'),
(10, '1.3.4', 'Issues with campaigns have been fixed, and proxy scanning and distribution with other services have been improved.', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', NULL, 1, 0, '2026-04-15 21:21:10'),
(11, '1.3.5', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', NULL, 0, 0, '2026-04-15 21:48:52'),
(12, '1.3.4', '', '', '', 'external', '', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', NULL, 1, 0, '2026-04-15 23:10:39'),
(13, '1.3.4', '', '', '', 'external', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', NULL, 0, 0, '2026-04-15 23:11:51'),
(14, '1.3.5', '', '', '', 'external', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', NULL, 0, 0, '2026-04-15 23:12:48'),
(15, '1.3.5', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', NULL, 0, 0, '2026-04-15 23:13:39'),
(16, '1.34', '', '', '', 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', '', '', '96.9 MB', 1, 0, '2026-04-15 23:55:10'),
(17, '1.3.4', 'Create unlimited campaigns with URL lists, target visits, duration, and scheduling.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.4.exe', NULL, NULL, NULL, '96.9 MB', 0, 0, '2026-04-16 00:03:22'),
(18, '1.3.5', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.5.exe', NULL, NULL, NULL, '96.93 MB', 0, 0, '2026-04-16 18:14:01'),
(19, '1.3.6', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-1.3.6.exe', NULL, NULL, NULL, '91.4 MB', 0, 0, '2026-04-18 15:03:12'),
(20, '2.0.0', '🎉 Major Update - Version 2.0.0\r\nNew Features:\r\n\r\nPrivacy Policy Acceptance - Added privacy policy page during installation\r\nMobile Video Auto-Play - Automatic video playback on mobile devices\r\nAudio Muting - Automatic audio muting for all campaigns\r\nEnhanced System Monitor - Added CPU & GPU monitoring panel\r\nImproved Alert Window - Better single-instance alert design\r\nMulti-language Support - Arabic, English, and French support\r\nBug Fixes:\r\n\r\nFixed ES module require error\r\nFixed Cannot create BrowserWindow error\r\nResolved single instance lock issues\r\nFixed white window issue in alerts\r\nTechnical:\r\n\r\nUpgraded to version 2.0.0\r\nEnhanced NSIS installer\r\nImproved YouTube video parameters\r\nBetter error handling\r\nSystem Requirements:\r\n\r\nWindows 10 or later\r\n4GB RAM minimum\r\n500MB free disk space', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-2.0.0.exe', NULL, NULL, NULL, '91.42 MB', 0, 0, '2026-04-19 17:35:14'),
(21, '2.0.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-2.0.0.exe', NULL, NULL, NULL, '91.4 MB', 0, 0, '2026-04-20 21:32:44'),
(22, '2.0.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-2.0.0.exe', NULL, NULL, NULL, '91.4 MB', 0, 0, '2026-04-22 21:35:45'),
(23, '2.3.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-2.3.0.exe', NULL, NULL, NULL, '94.3 MB', 0, 0, '2026-04-26 18:30:09'),
(24, '2.5.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-2.5.0.exe', NULL, NULL, NULL, '96.6 MB', 0, 0, '2026-05-02 19:55:26'),
(25, '2.3.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-2.3.0.exe', NULL, NULL, NULL, '94.3 MB', 0, 0, '2026-05-03 17:32:29'),
(26, '2.3.5', '- The interface has been improved and the program structure developed.\r\n\r\n- The traffic logic has been improved for greater anonymity.\r\n- The proxy scanning feature has been improved.\r\n- New devices and browsers have been added.\r\n- UTM campaigns have been added.\r\n- Search engine optimization has been added and improved.\r\n- An AI service has been added to answer all questions.\r\n- The live tracking section has been improved.\r\n- Detailed help windows have been added for each settings card.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro2.3.5.exe', NULL, NULL, NULL, '96.6 MB', 0, 0, '2026-05-08 23:36:03'),
(27, '3.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.0.0.exe', NULL, NULL, NULL, '96.6 MB', 0, 0, '2026-05-10 19:35:59'),
(28, '3.1.0', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.0.exe', NULL, NULL, NULL, '101 MB', 0, 0, '2026-05-14 19:36:39'),
(29, '3.1.1', '- The version has been updated and traffic issues have been fixed.\r\n- Cookie consent and ad blocking have been improved.\r\n- The timing of traffic issues has been resolved.\r\n- Strong protection and anonymity have been added to ensure the security of your advertising accounts.\r\n- Duplicate proxy detection has been added, proxy filtering has been improved, and continuous background scanning is now performed.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.1.exe', NULL, NULL, NULL, '97.1 MB', 0, 0, '2026-05-17 20:24:24'),
(30, '3.1.2', '- Fixed the issue of visits freezing.\r\n\r\n- Fixed the issue of slow scrolling and illogical navigation.\r\n- Fixed the issue of visits from search engines.\r\n- Added enhanced protection and more precise anonymity.\r\n- Added automatic proxy scanning.\r\n- Added the ability to upload proxy files and other proxy sources.\r\n- Checked visit timing and ensured its implementation.\r\n- Corrected bugs and added an hourly background cache clearing feature.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.2.exe', NULL, NULL, NULL, '97.1 MB', 0, 0, '2026-05-20 21:54:16'),
(31, '3.1.2.1', '- Fixed the issue of visits freezing.\r\n\r\n- Fixed the issue of slow scrolling and illogical navigation.\r\n- Fixed the issue of visits from search engines.\r\n- Added enhanced protection and more precise anonymity.\r\n- Added automatic proxy scanning.\r\n- Added the ability to upload proxy files and other proxy sources.\r\n- Checked visit timing and ensured its implementation.\r\n- Corrected bugs and added an hourly background cache clearing feature.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.2.exe', NULL, NULL, NULL, '96.9 MB', 0, 0, '2026-05-21 17:49:49'),
(32, '3.1.2', '- Fixed the issue of visits freezing.\r\n\r\n- Fixed the issue of slow scrolling and illogical navigation.\r\n- Fixed the issue of visits from search engines.\r\n- Added enhanced protection and more precise anonymity.\r\n- Added automatic proxy scanning.\r\n- Added the ability to upload proxy files and other proxy sources.\r\n- Checked visit timing and ensured its implementation.\r\n- Corrected bugs and added an hourly background cache clearing feature.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.2.exe', NULL, NULL, NULL, '97.1 MB', 0, 0, '2026-05-21 17:51:11'),
(33, '3.1.21', '🚀 New Features:**\r\n\r\n- **System Tray Support** - App now minimizes to system tray instead of closing, allowing campaigns to run in the background\r\n- **Double-click tray icon** to restore the main window\r\n- **Right-click tray menu** with \"Show Window\" and \"Quit\" options\r\n\r\n---\r\n\r\n**🔧 Improvements:**\r\n\r\n- **Proxy Auto-Check Interval** - Changed from 5 minutes to 1 hour for better performance\r\n- **Initial Proxy Check** - Automatic proxy check now runs when the app starts (after 15 seconds)\r\n- **\"All\" Option for Chips** - Added \"All\" button for Search Engines and Social Media platforms selection\r\n- **Internal Links Toggle** - Added option to enable/disable opening internal links in Navigation & Links card\r\n\r\n---\r\n\r\n**🐛 Bug Fixes:**\r\n\r\n- **Traffic Source Settings** - Fixed issue where Search Engines and Social Media platforms selections were not being saved\r\n- **Date.now() Error** - Fixed React purity error in Monitor.tsx by using lazy state initializer\r\n- **Database Schema** - Added `search_engine_names` and `social_platforms` columns to properly save traffic source selections\r\n\r\n---\r\n\r\n**📝 Database Changes:**\r\n\r\n- Added `search_engine_names` column to campaigns table\r\n- Added `social_platforms` column to campaigns table\r\n\r\n---\r\n\r\n**🔄 UI/UX Changes:**\r\n\r\n- Window close behavior changed - now hides to tray instead of quitting\r\n- App stays running in background when window is closed\r\n- Version number updated to 3.1.21', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.21.exe', NULL, NULL, NULL, '97.1 MB', 0, 0, '2026-05-24 15:39:05'),
(34, '3.1.22', 'Fixed AdSense cookie window and ad display issues\r\nFixed minor bugs for smoother operation', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.22.exe', NULL, NULL, NULL, '97.1 MB', 0, 0, '2026-05-24 19:50:13'),
(35, '3.1.24', 'SAM Traffic Pro Update Notes — v3.1.23\r\nYesterday (Dashboard & Real Data)\r\nSEO Performance Overview — Made dynamic with visit-duration filtering (5s–60s+)\r\nRecent Events — Replaced static mock data with real visit logs from the database; removed non-functional \"View All\" button\r\nTop Countries — Replaced with Acquisition Channels card showing real referrer-grouped traffic (organic, direct, social, referral)\r\nTraffic Distribution Map — Replaced wireframe globe with a real world map via react-simple-maps, accurate country markers, and color-coded visit density\r\nBrowser Breakdown — Real browser traffic distribution with branded icons and colors\r\nStats Row — Real percentage trends with directional arrows per metric\r\nTraffic Overview Chart — Real hourly / weekly / monthly visit data instead of randomized simulation\r\nResponsive Layout — Fixed card overlap on window resize; dashboard now flows naturally across all screen sizes\r\nToday (Live Monitor & IPC)\r\nLive Visit Log — Clear Button — Fixed Clear so it actually wipes logs from the Main Process memory, not just React local state\r\nIPC Bridge — Added traffic:clearLogs endpoint across', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.23.exe', NULL, NULL, NULL, '96.2 MB', 0, 0, '2026-06-01 18:37:09'),
(36, '3.1.23', 'SAM Traffic Pro Update Notes — v3.1.23\r\nYesterday (Dashboard & Real Data)\r\nSEO Performance Overview — Made dynamic with visit-duration filtering (5s–60s+)\r\nRecent Events — Replaced static mock data with real visit logs from the database; removed non-functional \"View All\" button\r\nTop Countries — Replaced with Acquisition Channels card showing real referrer-grouped traffic (organic, direct, social, referral)\r\nTraffic Distribution Map — Replaced wireframe globe with a real world map via react-simple-maps, accurate country markers, and color-coded visit density\r\nBrowser Breakdown — Real browser traffic distribution with branded icons and colors\r\nStats Row — Real percentage trends with directional arrows per metric\r\nTraffic Overview Chart — Real hourly / weekly / monthly visit data instead of randomized simulation\r\nResponsive Layout — Fixed card overlap on window resize; dashboard now flows naturally across all screen sizes\r\nToday (Live Monitor & IPC)\r\nLive Visit Log — Clear Button — Fixed Clear so it actually wipes logs from the Main Process memory, not just React local state\r\nIPC Bridge — Added traffic:clearLogs endpoint across', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.23.exe', NULL, NULL, NULL, '96.2 MB', 0, 0, '2026-06-01 18:38:55'),
(37, '3.1.22', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.22.exe', NULL, NULL, NULL, '96.9 MB', 0, 0, '2026-06-01 20:25:28');
INSERT INTO `app_updates` (`id`, `version`, `release_notes`, `developer_message`, `update_news`, `download_mode`, `direct_url`, `external_url`, `uploaded_file_path`, `uploaded_file_name`, `file_size`, `force_update`, `is_active`, `created_at`) VALUES
(38, '3.1.23', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro.V3.1.23.exe', NULL, NULL, NULL, '96.2 MB', 0, 0, '2026-06-01 21:45:38'),
(39, '3.1.25', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro.V3.1.23.exe', NULL, NULL, NULL, '', 0, 0, '2026-06-12 17:31:29'),
(40, '3.1.23', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro.V3.1.23.exe', NULL, NULL, NULL, '96.9 MB', 0, 0, '2026-06-12 17:32:12'),
(41, '3.1.24', '- Added ad click functionality\r\n- Improved search engine functionality\r\n- Improved browser fingerprinting functionality\r\n- Added RAM optimization functionality\r\n- Improved cache clearing functionality\r\n- Removed all processes that caused program crashes', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.24.exe', NULL, NULL, NULL, '91.9 MB', 0, 0, '2026-06-15 20:49:24'),
(42, '3.1.23', '', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro.V3.1.23.exe', NULL, NULL, NULL, '96.2 MB', 0, 0, '2026-06-15 22:19:45'),
(43, '3.1.24', '- Added the ability to click on AdSense ads with Smart Browsing.\r\n\r\n- Added the ability to click on Google Shopping products with Smart Browsing.\r\n- Fixed search engine issues.\r\n- Improved web campaign settings cards with the addition of a manual card organization option.', NULL, NULL, 'direct', 'https://github.com/massoudi67/samtrafficpro/releases/download/traffic-web/SAM-Traffic-Pro-Setup-3.1.24.exe', NULL, NULL, NULL, '92 MB', 0, 1, '2026-06-21 20:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price_regular` decimal(10,2) NOT NULL,
  `price_discount` decimal(10,2) DEFAULT NULL,
  `is_discount_active` tinyint(1) NOT NULL DEFAULT 0,
  `duration_text` varchar(255) NOT NULL DEFAULT 'Lifetime',
  `plan_type` varchar(50) NOT NULL DEFAULT 'paid',
  `features` text NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp_text` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `price_regular`, `price_discount`, `is_discount_active`, `duration_text`, `plan_type`, `features`, `display_order`, `is_active`, `is_featured`, `whatsapp_text`, `created_at`, `updated_at`) VALUES
(1, 'Free Trial', '55.00', '25.00', 1, '2 Days', 'trial', '[\"all_features_unlocked\"]', 0, 1, 1, '', '2026-06-06 19:49:16', '2026-07-25 15:03:48'),
(2, 'One-year license', '120.00', '70.00', 1, '1 year', 'paid', '[\"all_features_unlocked\",\"instant_activation\",\"secure_payment\"]', 2, 1, 0, 'Hi, I want to buy SAM Traffic Pro.', '2026-06-06 19:49:16', '2026-07-25 15:03:11'),
(3, 'Lifetime License', '300.00', '115.00', 1, 'Lifetime / 3PC', 'paid', '[\"all_features_unlocked\",\"lifetime_license\",\"free_updates_forever\",\"priority_support\",\"device_transfer\",\"instant_activation\",\"secure_payment\"]', 3, 1, 0, '', '2026-06-06 19:53:49', '2026-07-25 15:02:41'),
(4, '3-month license', '55.00', '25.00', 1, '3 months', 'paid', '[\"all_features_unlocked\",\"priority_support\",\"instant_activation\"]', 1, 1, 1, 'Hi, I want to buy SAM Traffic Pro.', '2026-06-06 20:40:36', '2026-07-25 15:04:20');

-- --------------------------------------------------------

--
-- Table structure for table `proxy_lists`
--

CREATE TABLE `proxy_lists` (
  `id` int(11) NOT NULL,
  `proxy_text` varchar(512) NOT NULL,
  `protocol` varchar(20) NOT NULL DEFAULT 'http',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `is_working` tinyint(1) NOT NULL DEFAULT 0,
  `latency_ms` int(11) DEFAULT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `last_checked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proxy_providers`
--

CREATE TABLE `proxy_providers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `logo_url` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proxy_providers`
--

INSERT INTO `proxy_providers` (`id`, `name`, `url`, `logo_url`, `description`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bright Data', 'https://brightdata.com/proxy-types/datacenter-proxy', NULL, NULL, 0, 1, '2026-06-01 19:34:10', '2026-06-01 19:34:10'),
(2, 'Oxylabs', 'https://oxylabs.io/products/proxy-browser', NULL, NULL, 1, 1, '2026-06-01 19:34:10', '2026-06-01 19:34:10'),
(3, 'Scrapy Cloud', 'https://scrapinghub.com/scrapy-cloud', NULL, NULL, 2, 1, '2026-06-01 19:34:10', '2026-06-01 19:34:10');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(7965, 'trial_duration_hours', '2', '2026-04-26 17:51:02', '2026-07-24 19:36:18'),
(54904, 'proxy_url', 'https://raw.githubusercontent.com/massoudi67/samtrafficpro/refs/heads/main/proxy', '2026-06-01 19:29:55', '2026-07-22 21:22:16'),
(60012, 'trial_duration_unit', 'days', '2026-06-06 21:11:04', '2026-07-24 19:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `trial_device_claims`
--

CREATE TABLE `trial_device_claims` (
  `id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trial_device_claims`
--

INSERT INTO `trial_device_claims` (`id`, `device_id`, `email`, `user_id`, `created_at`) VALUES
(4, 'TW96aWxsYS81LjAgKE1hY2ludG9zaDsgSW50ZWwg', 'soknarealty@gmail.com', 38, '2026-04-06 02:06:56'),
(22, '590afb05567cc7b5e062eeec6e0f43a55628e901', 'sahovo2151@sskaid.com', 25, '2026-04-16 21:28:05'),
(23, '2cbb42d3c341906f08e267c3a4ce02719842fe52', 'server24@katlok.com', 22, '2026-04-17 01:48:50'),
(28, 'ba6405d76ab6711f59350be4b578944aa243f24b', 'nourldkgjdgl@gmail.com', 28, '2026-04-23 15:06:15'),
(31, '577056ee6d77c913d859653e4a74eb2a7abaa2ea', 'sabrinkhlil591@gmail.com', 29, '2026-04-28 20:22:00'),
(35, '0ad66ca2c0020a6a765dc9aae392c5bfd845ccd8', 'amansoftindia@gmail.com', 33, '2026-05-05 22:09:27'),
(40, '53f046818f1c80ffee99fa50bdb50a47840c614c', 'z.a.abdi81@gmail.com', 39, '2026-05-09 16:24:36'),
(46, 'a99d8be1aacdf30c1dac5d1181bd85eb5ebb60bf', 'armandkriat@gmail.com', 41, '2026-05-10 18:33:55'),
(49, 'ea4d7512d60bd95a03574636eb59acfe6fcade34', 'selectedadham@gmail.com', 45, '2026-05-13 18:26:31'),
(53, 'c9b631c103493d032e972c6deafc68989232e7f5', 'fatiza80@gmail.com', 48, '2026-05-16 02:40:42'),
(54, '7698b2cd44de70ef474cf4281ac129d1a0c066c4', 'said@gmail.com', 49, '2026-05-16 03:04:04'),
(62, 'ccd256d4311d72127755b0797615de1392fa880a', 'akegurubodi297@gmail.com', 54, '2026-05-16 18:03:47'),
(63, 'b025b8f307a28d85df36c30a21f2c6fbf53673c6', 'amirdaikh2006@gmail.com', 55, '2026-05-16 22:53:46'),
(64, 'eae7dfc3dc91b1a373ac4b1d2b0e26da9f34e7f3', 'jabersofi834@gmail.com', 56, '2026-05-17 21:02:48'),
(65, 'a2771867efdbe145a0c663d2d14e5c1c7fd889e1', 'agjjbbiby@gmail.com', 57, '2026-05-20 10:41:35'),
(66, '132ad09ba1a701e07aa7cc452d2675b32be8a179', 'younes.memr@gmail.com', 58, '2026-05-20 13:41:27'),
(67, 'ee017c767ac8e0b2699b532c4f9c670b789aa412', 'hesenramdan27@gmail.com', 59, '2026-05-20 15:55:07'),
(68, '37922527daa6e3b5877cd32fee65e11fb1b80fef', 'leveluplab551@gmail.com', 60, '2026-05-20 18:07:23'),
(69, 'dec39b321933bb19f70c58aa5c5df6499790a534', 'nowwaf@gmail.com', 62, '2026-05-21 07:06:37'),
(70, '1dbfe6984e48ade4737b30aa62f52aa0bd30c25c', 'zexsbom@gmail.com', 63, '2026-05-21 14:12:30'),
(71, '87589620881d9dfe9a2fd5a259999c524ee97f37', 'slymanrby250@gmail.com', 64, '2026-05-21 20:01:34'),
(72, '6714c852a3b88bda69a4f54ddccb08a3666e81ab', 'elzem2012@yahoo.com', 65, '2026-05-21 20:48:12'),
(73, '36874de6f308f64eb5c610e64c28c8c70785a1e5', 'nsiri209@gmail.com', 66, '2026-05-22 04:47:07'),
(74, 'c3022730c45c8035263c9f36953d158e7d3f193a', 'bwka9305@gmail.com', 67, '2026-05-22 12:34:17'),
(75, '628a71962fdf8ee02b04d206497247234b83eae7', 'omargiraa17@gmail.com', 68, '2026-05-22 13:50:53'),
(76, 'ad99515639c303dd4bdeb9697adf91f29a0247ec', 'lap256top@gmail.com', 69, '2026-05-22 18:39:10'),
(77, '8f6f9c6f16ff9a35d35456c60f7f379b105454d2', 'deihmisimox@gmail.com', 70, '2026-05-23 00:27:34'),
(78, 'dea942fa6ad1e25490d5346091edfda2732e4533', 'kari130675@gmail.com', 71, '2026-05-23 01:21:02'),
(79, '9bf3281bfcc6b83fab89f8caa90deac142e684a0', 'fouad007991@gmail.com', 72, '2026-05-23 07:24:14'),
(80, 'e1626d178b55b37474001cb1723d3db73c9060c0', 'berrachisaad07@gmail.com', 73, '2026-05-23 22:03:34'),
(81, '0d7bd57d7f781c0f25ef1cc7d65f5e85e8ae4bad', 'mmaahhmmeedd88@gmail.com', 74, '2026-05-25 04:37:02'),
(82, '91b9af4b2dccd9acee8b685e36780f66283eec04', 'jedelkami@gmail.com', 75, '2026-05-25 05:26:51'),
(83, 'dea3ff4b362550c979eeed883a56f578990e20b6', 'rzanalmghary1485555@gmail.com', 77, '2026-05-25 06:02:58'),
(84, '3f6726e57f42409eed4aa0a994460ef68c3f9296', 'bedibiy825@shortapk.com', 78, '2026-05-25 07:38:45'),
(85, 'dff7f593fdea25bda515e300b11d8d2245626d76', 'wjnimahzf7lz@ewebrus.com', 79, '2026-05-25 16:21:52'),
(86, '8b85cd8d00063d07eb88c2af94a13da6201530e0', 'moknimouhamed26@gmail.com', 80, '2026-05-25 18:17:36'),
(87, 'e43526b63bc256f0adad33c441dcf3fe281e2c7c', 'hedofec749@marineso.com', 81, '2026-05-25 20:35:40'),
(88, '8e00b24d50f2e2b67aabacabe8f3d6a2e7d8eac6', 'metoali77@gmail.com', 82, '2026-05-25 22:38:47'),
(89, '505dfa38ae31363c9354a12bbe928ace41595c27', 'mo@gmail.com', 83, '2026-05-26 01:51:36'),
(90, '64670147aa3eebb8cbb889ce177f8875bf2c435e', 'm.musallam84@yahoo.com', 84, '2026-05-26 02:25:32'),
(91, '238317f8c6dad20c170bb17252fc86848ef71527', 'winux2018@gmail.com', 85, '2026-05-26 04:18:35'),
(92, 'd8954af7594be57b48ef760d035d5eddc94b480b', 'mesterioman@gmail.com', 86, '2026-05-27 19:41:48'),
(93, '965966f6870823accbd11ae464b45e281eecc7df', 'rdpmac29@gmail.com', 87, '2026-05-28 10:39:15'),
(94, 'add3d0e40aecf7f4f7f150491a3cc4b94215598b', 'hosam.samara@gmail.com', 89, '2026-05-29 23:35:08'),
(95, '45578cfad912bc1c33ce5ef688cf7d2b25ac622a', 'sherifelgarhy2626@gmail.com', 90, '2026-05-30 16:01:32'),
(96, '0b6a683046f1201e9f51f550afb509f2ae3d1c3d', 'ahmed1301817@gmail.com', 91, '2026-05-31 00:12:13'),
(97, '0ad1a154f6ce1aded96c1b81b9663f3d0cd22dec', 'brightamohamedamine5@gmail.com', 92, '2026-06-01 05:42:20'),
(101, 'JPXx8MTQ3NDR8and5bGo1ZXV4YWN8bXB2bXBoN20', 'hordanayham@gmail.com', 96, '2026-06-02 02:56:37'),
(102, '9fHw3MDY0LjV8NjQ1ZWd4djFneXJ8bXB2dDVpaHM', 'dr.sameh.moohamed@gmail.com', 97, '2026-06-02 05:57:52'),
(103, 'wMDAwMDAwMTV8cmQ2M3U3MnBrMnB8bXB4OGxzeHM', 'alnaserallazina@gmail.com', 98, '2026-06-03 05:57:42'),
(104, '5OTk5OTA0NjN8OHJrNTdxemx4NmN8bXB4dWt6bHI', 'j.omefa.rit14@gmail.com', 99, '2026-06-03 16:12:29'),
(105, 'TW96aWxsYS81LjAgKFdpbmRvd3MgTlQgMTAuMDsg', 'omadomad212@gmail.com', 100, '2026-06-03 18:09:27'),
(106, '8MzY4MDQyLjV8emhpOHFoeWZxcGd8bXB5YjlrcDg', 'cinnewsvip@gmail.com', 101, '2026-06-04 00:00:41'),
(107, 'OTk5OTk5NzAyfGZwYmcxbmlscDA0fG1xMDd2NXJq', 'ehab.mohamed.055@gmail.com', 104, '2026-06-05 08:01:55'),
(108, 'AwMDAwMDI5OHwwdmhoZ3piZzd2c3xtcTJqZmljOA', 'mmaahhmmeedd84@gmail.com', 106, '2026-06-06 22:59:22'),
(112, 'MDAwMDAwNzQ1fHFpaXAzOHBoOGV0fG1xMm9qaHky', 'oudim982@gmail.com', 110, '2026-06-07 01:22:40'),
(114, 'OTk5OTk5MjU1fGQ4dnRwMXhvcDFwfG1xMnB5bnIx', 'm00174817@gmail.com', 112, '2026-06-07 02:02:27'),
(116, '11ad025bb3448490f3200b5a34fb7e47bc7eabdc', 'massoudinour03@gmail.com', 115, '2026-06-08 17:32:59'),
(117, 'e6db3ff0c7bafd175ff31aa6354f2b1ba49a9864', 'rachidsbaa2222@gmail.com', 116, '2026-06-08 20:18:53'),
(118, '8168bf460ee6e1320dc491b9617ecabb9c2a8256', 'ammariabdelilahammari@gmail.com', 117, '2026-06-09 23:05:57'),
(119, '24cd45c944afc039943018a488cb0b3428c1292f', 'qalzeze@gmail.com', 118, '2026-06-12 17:28:16'),
(120, '9aef51f6de204525c4bf30da226b1537239c6ed4', 'omidy2188@gmail.com', 119, '2026-06-13 04:51:59'),
(121, 'a1d1bebe464ec2236e76b99fdceefa001d1904d1', 'mrslayers2120@gmail.com', 121, '2026-06-14 14:08:51'),
(122, 'fc8bf34e848b07bc2dd87a68dfe617001640a657', 'capac.i.ty.nm.w@gmail.com', 122, '2026-06-16 02:50:44'),
(123, 'cbacba1eccbeebd6ca53e26874be5d7e039a4966', 'annehoff.m.an86025@gmail.com', 124, '2026-06-20 17:48:48'),
(124, '90e58ba77489bca035657c4486d9de9f89fa296e', 'mobillk10@gmail.com', 126, '2026-06-24 19:33:09'),
(125, '29792e1300d398880581c35b98bc0f1f1bf4117a', 'emoss4231@gmail.com', 128, '2026-06-28 20:16:55'),
(126, '88d3d987a1a87a16a9dfdc32bf12c696bbdfb805', 'ahmedsalama201567@gmail.com', 131, '2026-07-09 20:54:03'),
(127, '866f7ff39435be0d0b50185da4f074554ce537a3', 'snist14d@gmail.com', 136, '2026-07-23 15:34:26'),
(128, 'a4723ef4ea831e65e182586ddad7dd1f00ae6b9f', 'baskaran31855@gmail.com', 137, '2026-07-23 16:38:42'),
(129, '8b5e6da0a3b2e019ffbf94cceca3b565389ecec9', 'hamzamokmed171@gmail.com', 138, '2026-07-24 20:10:24');

-- --------------------------------------------------------

--
-- Table structure for table `trial_verifications`
--

CREATE TABLE `trial_verifications` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `client_ip` varchar(45) NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trial_verifications`
--

INSERT INTO `trial_verifications` (`id`, `token`, `email`, `device_id`, `client_ip`, `country_code`, `verified_at`, `expires_at`, `created_at`) VALUES
(1, 'db363962cae1aa8faaf05d22fd35cf4d5c7fa001367e39db9fb01cc27dd07cf3', 'massoudisameh@gmail.com', 'TW96aWxsYS81LjAgKFdpbmRvd3MgTlQgMTAuMDsg', '197.26.114.73', 'TN', '2026-06-02 02:37:38', '2026-06-02 03:37:08', '2026-06-01 19:37:09'),
(2, '5d23f80a35ef2db7fdb6fefa22c22a79cbe8a6399c5cf27f9d6295107ecd90c8', 'manusai030321@gmail.com', 'wMDAwMDExMTh8Z3gxbHFjY2ozcTh8bXB2bWNwNXA', '197.26.114.73', 'TN', '2026-06-02 02:46:13', '2026-06-02 03:46:01', '2026-06-01 19:46:01'),
(3, 'c64c4fb882b1d58a4bc9ad2b076bbb24713c8b322ac51098d6b464a41d677455', 'hordanayham@gmail.com', 'JPXx8MTQ3NDR8and5bGo1ZXV4YWN8bXB2bXBoN20', '197.26.114.73', 'TN', '2026-06-02 02:56:37', '2026-06-02 03:55:56', '2026-06-01 19:55:56'),
(4, '28ff65c2cfbb7a6815083905d8fbddb6529302bb535fc8bb0e5fdfbff77acd8c', 'dr.sameh.moohamed@gmail.com', '9fHw3MDY0LjV8NjQ1ZWd4djFneXJ8bXB2dDVpaHM', '156.197.242.53', 'EG', '2026-06-02 05:57:52', '2026-06-02 06:56:25', '2026-06-01 22:56:26'),
(5, '4a9c8b816d1d0d8bd55b6b6782464ffb7a8c2e98d67b3e6efb874961fba5d4cc', 'alnaserallazina@gmail.com', 'wMDAwMDAwMTV8cmQ2M3U3MnBrMnB8bXB4OGxzeHM', '105.108.53.204', 'DZ', '2026-06-03 05:57:42', '2026-06-03 06:56:45', '2026-06-02 22:56:45'),
(6, '9ac96d6ae99fd16f3137f41f240baa2e62624a2277af635f40c2f5dd9490cf7b', 'j.omefa.rit14@gmail.com', '5OTk5OTA0NjN8OHJrNTdxemx4NmN8bXB4dWt6bHI', '196.117.80.51', 'MA', '2026-06-03 16:12:29', '2026-06-03 17:11:59', '2026-06-03 09:11:59'),
(7, '644847a3ff2b37fd3e4ddc75618c29713933a4242aaa68bf45d2032c8319571c', 'omadomad212@gmail.com', 'TW96aWxsYS81LjAgKFdpbmRvd3MgTlQgMTAuMDsg', '41.100.39.47', 'DZ', '2026-06-03 18:09:27', '2026-06-03 19:06:11', '2026-06-03 11:06:10'),
(8, '9acf82c74760f4402c3d5e94a0b3e893cbf5004c8aa54e003814f05cf330c188', 'hhyhhhhgygg@gmail.com', 'OTk5OTg4MDc5fDNydmdmajQ2aGk4fG1weTllcWsw', '196.139.233.158', 'EG', NULL, '2026-06-04 00:07:03', '2026-06-03 16:07:04'),
(9, '4555f8a97dc2d6291801d1e458eccce1f99b78e0081beaa36b2613e991b8f580', 'cinnewsvip@gmail.com', '8MzY4MDQyLjV8emhpOHFoeWZxcGd8bXB5YjlrcDg', '160.179.54.54', 'MA', '2026-06-04 00:00:41', '2026-06-04 00:59:00', '2026-06-03 16:59:00'),
(10, '8a6c32023c2d691bc89f344af66684b5852a2cf585ab0ed05d72ae643dffebb0', 'ehab.mohamed.055@gmail.com', 'OTk5OTk5NzAyfGZwYmcxbmlscDA0fG1xMDd2NXJq', '156.211.76.120', 'EG', '2026-06-05 08:01:55', '2026-06-05 08:59:21', '2026-06-05 00:59:22'),
(11, '457ca849758f7e8b9be2efafe6647bc202dcf31a883c6b70903491078abffd95', 'mmaahhmmeedd84@gmail.com', 'AwMDAwMDI5OHwwdmhoZ3piZzd2c3xtcTJqZmljOA', '196.159.215.227', 'EG', '2026-06-06 22:59:22', '2026-06-06 23:58:41', '2026-06-06 15:58:42'),
(12, '558f14bd2c2259b6c7b9c22d70640fa91ac647aad52d2ef8ceca7d36f989696a', 'massoudisameh08@gmail.com', 'MDAwMDAwNzQ1fDN6aXFodjg0MmYyfG1xMm1uc2V2', '197.15.44.114', 'TN', '2026-06-07 00:29:30', '2026-06-07 01:29:00', '2026-06-06 17:29:00'),
(13, '33f5ce2c3d74c8145e0f26db0bb9b8b01bb5c2c5e4e961f3c38c5aa3c498ab67', '67sameh@gmail.com', '5OTk5OTkyNTV8dXZmMDBjejZpeWp8bXEybXRyNDA', '212.116.83.97', 'SE', '2026-06-07 00:36:57', '2026-06-07 01:34:38', '2026-06-06 17:34:38'),
(14, '1452df06e37f00514df3f5c48c2d5ccf8163695a2dc6e5cd422a6b962328b8fd', 'oudim982@gmail.com', 'MDAwMDAwNzQ1fHFpaXAzOHBoOGV0fG1xMm9qaHky', '213.89.119.57', 'SE', '2026-06-07 01:22:40', '2026-06-07 02:22:23', '2026-06-06 18:22:23'),
(15, '9cd7708fc42c28797c2397b590817a79dd24efebc83cefe9bc571732b1a513db', 'manusumpro@gmail.com', 'x8MzU4MjAuNXx2aGJ0M2V1bXJ0Z3xtcTJwOWp6aw', '84.74.63.246', 'CH', '2026-06-07 01:42:48', '2026-06-07 02:41:56', '2026-06-06 18:41:56'),
(16, '2db3bce7ccfafa79ccf2e883da8ecc18a5c880285d455f38d3e6e09577ae6c89', 'm00174817@gmail.com', 'OTk5OTk5MjU1fGQ4dnRwMXhvcDFwfG1xMnB5bnIx', '157.143.81.132', 'CH', '2026-06-07 02:02:27', '2026-06-07 03:01:26', '2026-06-06 19:01:26'),
(17, '108a24f809d34ee44fa44a49fb8150bd9fef376cafd36f780db707ea6c0b4e2b', 'massoudisameh03@gmail.com', '', '197.26.112.73', 'TN', NULL, '2026-06-09 01:26:43', '2026-06-08 17:26:43'),
(18, '0897421135a678bde78f9ef1f148225837f9a9f3ec119f65250c01d1fb1829d4', 'massoudinour03@gmail.com', '', '197.26.112.73', 'TN', '2026-06-09 00:27:20', '2026-06-09 01:27:00', '2026-06-08 17:27:00'),
(19, '29319b2f43af8a9c5e52a73119fced5b1bb7a6c56722dba5562442d622773317', 'massoudinour03@gmail.com', '', '197.26.112.73', 'TN', '2026-06-09 00:32:53', '2026-06-09 01:32:45', '2026-06-08 17:32:45'),
(20, '133c814021e3836ea5e573753dbd8bf7d7f0cc86124dde0056b3005402066fda', 'rachidsbaa2222@gmail.com', '', '196.74.110.49', 'MA', '2026-06-09 03:12:28', '2026-06-09 03:54:25', '2026-06-08 19:54:26'),
(21, '0fb52da44a25d65d1dbafb57b397eececac9cd3b92a4b09fdf4d12da2e63514c', 'rochdiosalh@gmail.com', '', '196.74.110.49', 'MA', NULL, '2026-06-09 04:04:25', '2026-06-08 20:04:25'),
(22, '6a5773911c0cb4e1046359507acebc1b77f1e384ecfd44368067c97add7e0f71', 'ammariabdelilahammari@gmail.com', '', '105.76.186.26', 'MA', '2026-06-10 06:05:16', '2026-06-10 07:04:25', '2026-06-09 23:04:25'),
(23, '4ade9628362d986e40fb98fe5195ae6482a2b4da2cbc5ddaabb2883eba9466fb', 'massoudisameh06@gmail.com', '', '197.15.245.205', 'TN', NULL, '2026-06-12 02:18:56', '2026-06-11 18:18:55'),
(24, '2d1afffcf839ae71bada718d7fa46f393707af7c1f5c5b5d0eae393f85136a27', 'alzez3030@gmail.com', '', '31.203.149.153', '', NULL, '2026-06-13 01:13:55', '2026-06-12 17:13:55'),
(25, '4914a4bcdf8246d8973515f44833a68b634b61a952044cb655af0200d4cf7b2a', 'qalzeze@gmail.com', '', '31.203.149.153', 'KW', '2026-06-13 00:28:09', '2026-06-13 01:24:54', '2026-06-12 17:24:54'),
(26, '8457c2e0b8b260c7e17ac9e688238fc8bc790121852092fee1e241612835f51b', 'omidy2188@gmail.com', '', '41.100.136.68', 'DZ', '2026-06-13 11:51:51', '2026-06-13 12:49:09', '2026-06-13 04:49:09'),
(27, '76af5f2dc79e6d3f47defdf98feea190d12a23940914a9821290b1227980629d', 'meghazi47anouar@gmail.com', '', '197.200.27.224', 'DZ', '2026-06-14 20:48:32', '2026-06-14 21:47:50', '2026-06-14 13:47:49'),
(28, 'dcd2ef52f3fb0157890374405965d95f53de81ecd9d6d52a0fde75a5812226b4', 'mrslayers2120@gmail.com', '', '197.200.27.224', 'DZ', '2026-06-14 21:08:43', '2026-06-14 22:08:27', '2026-06-14 14:08:27'),
(29, '7c6f0ce3301ffffc250d67805c029dea917cff6a0457d70719c93444c970b075', 'capac.i.ty.nm.w@gmail.com', '', '154.241.1.10', 'DZ', '2026-06-16 09:49:57', '2026-06-16 10:49:31', '2026-06-16 02:49:31'),
(30, '2d48bdc857511e5f9d62591fbef4ffda2b9c335ba75f7b4cd06815237e2dcceb', 'omadomad212@gmail.com', '', '41.100.230.36', 'DZ', '2026-06-18 18:08:07', '2026-06-18 19:07:33', '2026-06-18 11:07:32'),
(31, '027ac9e55a9c213e4226199853403f4b2804caf9540aa2346fbcf3fb349fbc0b', 'mhdjiuo@gmail.com', '', '196.89.171.200', 'MA', NULL, '2026-06-18 21:15:37', '2026-06-18 13:15:36'),
(32, 'd4ab96ff52b06c85b1f6db30e99ab4bbb00d22dc0740af3bf001b0dac8ae685a', 'melanieranda.ll.8.21.2@gmail.com', '', '41.238.213.91', 'EG', NULL, '2026-06-21 01:18:35', '2026-06-20 17:18:35'),
(33, '0cf189a8cf6cea0dc6600d541e4c22eda580b3f404f0d854681198433554d7a3', 'annehoff.m.an86025@gmail.com', '', '41.238.213.91', 'EG', '2026-06-21 00:48:24', '2026-06-21 01:46:00', '2026-06-20 17:46:00'),
(34, 'e34fa4424e5a3be951b71d2407b34754912261d7dd021397bae88a0c27c568d7', 'mobillk10@gmail.com', '', '156.211.30.27', 'EG', '2026-06-25 02:33:03', '2026-06-25 03:32:17', '2026-06-24 19:32:16'),
(35, 'ea847c4f917a7b927cc54885af897137180b7fbf2057f63eed49c016d9167246', 'winux2018@gmail.com', '', '105.104.104.35', 'DZ', '2026-06-29 01:21:08', '2026-06-29 02:20:19', '2026-06-28 18:20:19'),
(36, 'a301606b5daf138d88b10d0ef4b7c7afdb91e894f5a31f469ec189513b830cf9', 'emoss4231@gmail.com', '', '159.146.40.238', 'TR', '2026-06-29 03:12:54', '2026-06-29 04:12:38', '2026-06-28 20:12:38'),
(37, '02b1ab66fcfbfacb372b8e528418c9d6ce6c2d68af8944d89a6ffbfba82ba6f6', 'tour3764@gmail.com', '', '41.200.10.32', 'DZ', NULL, '2026-06-29 16:48:00', '2026-06-29 08:48:00'),
(38, '4895a96c222eff20eb91e1763189892eff49b5069c1802cda85469edaab7613e', 'ahmedsalama201567@gmail.com', '', '41.237.36.141', 'EG', '2026-07-10 03:53:56', '2026-07-10 04:52:41', '2026-07-09 20:52:41'),
(39, 'dc548f038ee841876bc745a8cf1c9d96ae0eea268be7f369522a684abeef9eec', 'pitamghosh8@gmail.com', '', '223.184.142.221', 'IN', NULL, '2026-07-20 15:45:27', '2026-07-20 07:45:28'),
(40, '0d9a57e0960a7b0b74a735dba985548b6c1cb5b258d1d47883575fd8fecafa1b', 'massoudisameh07@gmail.com', '', '41.225.115.247', 'TN', NULL, '2026-07-20 22:46:05', '2026-07-20 14:46:05'),
(41, 'afd25106e5d23fa9955412d74d3d278a802615d1267354d4db3f1b0dff24560f', 'pitamghosh8@gmail.com', '', '223.184.142.221', 'IN', NULL, '2026-07-20 22:49:51', '2026-07-20 14:49:51'),
(45, '4a3960a32fc8eb2f877c682cdcb787ba2685e0336201c6915727dd0634021935', 'antigrav0303@gmail.com', '', '197.25.172.72', 'TN', NULL, '2026-07-20 23:28:55', '2026-07-20 15:28:55'),
(46, '8f3a7712c85c73276ee3f29445453f766ed5a57906b3961372c0112b67194f5f', 'treaai41@gmail.com', '', '81.220.254.93', 'FR', '2026-07-20 22:34:51', '2026-07-20 23:32:51', '2026-07-20 15:32:51'),
(47, '51e1a7290c385bf81151b2b7078567c8fb605f486f8869877b84e9978204ee68', 'webmail.fsb@gmail.com', '', '105.68.181.144', 'MA', '2026-07-23 03:48:51', '2026-07-23 04:22:03', '2026-07-22 20:22:03'),
(48, 'dcbe43cfa05928d77573dfbeccfd093b858a943d01b97d9458411a724d1c8fc2', 'snist14d@gmail.com', '', '49.204.9.135', 'IN', '2026-07-23 22:34:15', '2026-07-23 23:31:06', '2026-07-23 15:31:06'),
(49, '9e381ec05aec179055ecab3a3e2ff19c6c45e2b19a551da0af88e5ae83b918b7', 'baskaran31855@gmail.com', '', '202.141.46.250', 'IN', '2026-07-23 23:38:23', '2026-07-24 00:37:06', '2026-07-23 16:37:06'),
(50, 'a12c7b6608ab91d367aeea09b796764e13c5aee7c517fcc0dfd4d764b30e6d8d', 'hamzamokmed171@gmail.com', '', '197.119.94.109', 'DZ', '2026-07-25 03:10:14', '2026-07-25 04:09:17', '2026-07-24 20:09:17'),
(51, 'cf9e8084fd9a9ab9cdf24b5a04448c3035fa8f5a2a18b0a0976566b4aad94642', 'evtechor25@gmail.com', '', '103.15.61.51', 'IN', '2026-07-25 18:07:36', '2026-07-25 19:07:10', '2026-07-25 11:07:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `activation_code` varchar(255) NOT NULL,
  `account_type` varchar(50) NOT NULL DEFAULT 'paid',
  `trial_email` varchar(255) DEFAULT NULL,
  `trial_started_at` datetime DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `activation_duration_days` int(11) NOT NULL DEFAULT 0,
  `max_devices` int(11) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `last_seen_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `activation_code`, `account_type`, `trial_email`, `trial_started_at`, `trial_ends_at`, `device_id`, `country_code`, `last_ip`, `status`, `activation_duration_days`, `max_devices`, `expires_at`, `activated_at`, `last_seen_at`, `created_at`) VALUES
(43, 'shujaa321s@gmail.com', '', '21ED5A-4B1321-17CFD4-6FE73C', 'paid', NULL, NULL, NULL, '09ed0cee08d50774006c9d709083ae97aaaf8f40', 'SA', '79.170.122.232', 'active', 0, 1, NULL, '2026-05-12 19:00:44', '2026-07-17 22:24:13', '2026-05-12 10:03:22'),
(61, 'admin', '', '9C8178-6347C7-60FA8B-2BA568', 'paid', NULL, NULL, NULL, '11ad025bb3448490f3200b5a34fb7e47bc7eabdc', 'TN', '41.225.88.13', 'active', 0, 1, NULL, '2026-05-20 21:56:40', '2026-07-25 16:59:17', '2026-05-20 21:56:32'),
(76, 'Deihmi Abdessmaed', '', '94DF65-EAAA35-DFB526-7BFD01', 'paid', NULL, NULL, NULL, '8f6f9c6f16ff9a35d35456c60f7f379b105454d2', 'DZ', '197.205.214.2', 'active', 90, 1, '2026-08-22 22:31:10', '2026-05-24 22:34:47', '2026-05-26 10:53:25', '2026-05-24 22:31:11'),
(102, 'nidhal', '', '6AEBB4-193EB0-75F032-7514A4', 'paid', NULL, NULL, NULL, 'ee2bb6d85078b86588721a264bcf2d1362eb9616', 'TN', '197.238.69.217', 'active', 0, 1, NULL, '2026-06-04 16:32:31', '2026-06-04 16:42:01', '2026-06-04 16:31:38'),
(103, 'admin2', '', 'A364F5-609440-1B9025-2378BC', 'paid', NULL, NULL, NULL, '2cbb42d3c341906f08e267c3a4ce02719842fe52', 'TN', '197.15.224.242', 'active', 0, 1, NULL, '2026-06-04 19:05:49', '2026-06-22 18:44:14', '2026-06-04 19:05:31'),
(132, 'Pitam Ghosh', '', '27DA54-CDB605-9D9F80-FFAEFA', 'paid', NULL, NULL, NULL, '4beb51f566cac461679b5f94f905f68afe4df65b', 'IN', '165.101.250.103', 'active', 3, 1, '2026-07-23 14:52:48', '2026-07-20 15:20:52', '2026-07-21 16:42:08', '2026-07-20 14:52:48'),
(134, 'Trial - webmail.fsb@gmail.com', 'webmail.fsb@gmail.com', 'D26F00-5E4E5B-BEDB77-B04751', 'trial', 'webmail.fsb@gmail.com', '2026-07-22 20:48:51', '2026-07-25 20:48:51', NULL, 'MA', '105.68.181.144', 'active', 1, 1, '2026-07-25 20:48:51', NULL, '2026-07-22 20:48:51', '2026-07-22 20:48:51'),
(135, 'Yassine', '', 'D0A9AD-448817-5789C9-0A359E', 'paid', NULL, NULL, NULL, '629e6e574b16448215aa85431316ebdbdba78e81', 'MA', '105.68.181.144', 'active', 30, 1, '2026-08-21 21:08:35', '2026-07-22 21:14:30', '2026-07-22 22:55:45', '2026-07-22 21:08:36'),
(136, 'Trial - snist14d@gmail.com', 'snist14d@gmail.com', '525D75-2AC965-DE0003-6B5FEB', 'trial', 'snist14d@gmail.com', '2026-07-23 15:34:15', '2026-07-26 15:34:15', '866f7ff39435be0d0b50185da4f074554ce537a3', 'IN', '103.15.61.51', 'active', 1, 1, '2026-07-26 15:34:15', '2026-07-23 15:34:25', '2026-07-25 09:17:09', '2026-07-23 15:34:15'),
(137, 'Trial - baskaran31855@gmail.com', 'baskaran31855@gmail.com', '71B012-931956-37D281-9C2CC1', 'trial', 'baskaran31855@gmail.com', '2026-07-23 16:38:23', '2026-07-26 16:38:23', 'a4723ef4ea831e65e182586ddad7dd1f00ae6b9f', 'IN', '202.141.102.79', 'active', 1, 1, '2026-07-26 16:38:23', '2026-07-23 16:38:42', '2026-07-25 14:44:42', '2026-07-23 16:38:23'),
(138, 'Trial - hamzamokmed171@gmail.com', 'hamzamokmed171@gmail.com', '423E56-0E7236-FD274B-B82495', 'trial', 'hamzamokmed171@gmail.com', '2026-07-24 20:10:14', '2026-07-26 20:10:14', '8b5e6da0a3b2e019ffbf94cceca3b565389ecec9', 'DZ', '197.119.94.109', 'active', 1, 1, '2026-07-26 20:10:14', '2026-07-24 20:10:24', '2026-07-24 20:10:25', '2026-07-24 20:10:14'),
(139, 'Trial - evtechor25@gmail.com', 'evtechor25@gmail.com', '527E4C-F150C4-6E53A4-AB8A9C', 'trial', 'evtechor25@gmail.com', '2026-07-25 11:07:36', '2026-07-27 11:07:36', NULL, 'IN', '103.15.61.51', 'active', 1, 1, '2026-07-27 11:07:36', NULL, '2026-07-25 11:07:36', '2026-07-25 11:07:37'),
(140, 'Free Gaza', '', '140EF2-29E301-756634-F8E7F6', 'paid', NULL, NULL, NULL, NULL, NULL, NULL, 'active', 0, 1, NULL, NULL, NULL, '2026-07-25 11:29:59'),
(141, 'Vinay', '', '05CE6E-32ED09-9CF32F-64B42F', 'paid', NULL, NULL, NULL, '866f7ff39435be0d0b50185da4f074554ce537a3', 'IN', '103.175.182.103', 'active', 365, 1, '2027-07-25 15:45:09', '2026-07-25 15:46:19', '2026-07-25 15:46:22', '2026-07-25 15:45:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `first_activated_at` timestamp NULL DEFAULT current_timestamp(),
  `last_seen_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_devices`
--

INSERT INTO `user_devices` (`id`, `user_id`, `device_id`, `country_code`, `last_ip`, `first_activated_at`, `last_seen_at`, `created_at`) VALUES
(43, 43, '09ed0cee08d50774006c9d709083ae97aaaf8f40', 'SA', '79.170.122.232', '2026-05-12 19:00:44', '2026-07-17 22:24:14', '2026-05-12 19:00:44'),
(61, 61, '11ad025bb3448490f3200b5a34fb7e47bc7eabdc', 'TN', '41.225.88.13', '2026-05-20 21:56:40', '2026-07-25 16:59:17', '2026-05-20 21:56:40'),
(76, 76, '8f6f9c6f16ff9a35d35456c60f7f379b105454d2', 'DZ', '197.205.214.2', '2026-05-24 22:34:47', '2026-05-26 10:53:25', '2026-05-24 22:34:47'),
(102, 102, 'ee2bb6d85078b86588721a264bcf2d1362eb9616', 'TN', '197.238.69.217', '2026-06-04 16:32:31', '2026-06-04 16:42:01', '2026-06-04 16:32:31'),
(103, 103, '2cbb42d3c341906f08e267c3a4ce02719842fe52', 'TN', '197.15.224.242', '2026-06-04 19:05:48', '2026-06-22 18:44:13', '2026-06-04 19:05:48'),
(124, 132, '4beb51f566cac461679b5f94f905f68afe4df65b', 'IN', '165.101.250.103', '2026-07-20 15:20:52', '2026-07-21 16:42:08', '2026-07-20 15:20:52'),
(125, 135, '629e6e574b16448215aa85431316ebdbdba78e81', 'MA', '105.68.181.144', '2026-07-22 21:14:31', '2026-07-22 22:55:45', '2026-07-22 21:14:31'),
(126, 136, '866f7ff39435be0d0b50185da4f074554ce537a3', 'IN', '103.15.61.51', '2026-07-23 15:34:26', '2026-07-25 09:17:09', '2026-07-23 15:34:26'),
(127, 137, 'a4723ef4ea831e65e182586ddad7dd1f00ae6b9f', 'IN', '202.141.102.79', '2026-07-23 16:38:42', '2026-07-25 14:44:42', '2026-07-23 16:38:42'),
(128, 138, '8b5e6da0a3b2e019ffbf94cceca3b565389ecec9', 'DZ', '197.119.94.109', '2026-07-24 20:10:24', '2026-07-24 20:10:25', '2026-07-24 20:10:24'),
(129, 141, '866f7ff39435be0d0b50185da4f074554ce537a3', 'IN', '103.175.182.103', '2026-07-25 15:46:19', '2026-07-25 15:46:22', '2026-07-25 15:46:19');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_addresses`
--

CREATE TABLE `wallet_addresses` (
  `id` int(11) NOT NULL,
  `currency` varchar(50) NOT NULL,
  `network` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_addresses`
--

INSERT INTO `wallet_addresses` (`id`, `currency`, `network`, `address`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'USDT', 'BEP20', '0x3d8e593952a7adc3bffbd1e4bfd3bb563b9e66b6', 1, 1, '2026-05-09 19:28:04', '2026-05-09 19:29:35'),
(4, 'ETH', 'ERC20', '0x3d8e593952a7adc3bffbd1e4bfd3bb563b9e66b6', 3, 1, '2026-05-09 19:28:04', '2026-05-09 19:30:25'),
(17, 'USDT', 'TRC20', 'TXhGb5h9jPm82p6VtqMdWM1hTZrP8GxhVE', 0, 1, '2026-05-09 19:28:20', '2026-05-09 19:32:55'),
(35, 'BTC', '', '16pn5mTnxyJ6Uq6MtJrZYzT8JszZ9j1Ryb', 2, 1, '2026-05-09 19:28:29', '2026-05-09 19:29:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `ai_settings`
--
ALTER TABLE `ai_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `app_news_messages`
--
ALTER TABLE `app_news_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_updates`
--
ALTER TABLE `app_updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `proxy_lists`
--
ALTER TABLE `proxy_lists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proxy_text` (`proxy_text`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_working` (`is_working`);

--
-- Indexes for table `proxy_providers`
--
ALTER TABLE `proxy_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `trial_device_claims`
--
ALTER TABLE `trial_device_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`),
  ADD UNIQUE KEY `uk_device_id` (`device_id`);

--
-- Indexes for table `trial_verifications`
--
ALTER TABLE `trial_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD UNIQUE KEY `uk_token` (`token`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_device` (`device_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activation_code` (`activation_code`),
  ADD UNIQUE KEY `uk_activation_code` (`activation_code`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_user_device` (`user_id`,`device_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_currency_network` (`currency`,`network`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ai_settings`
--
ALTER TABLE `ai_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58010;

--
-- AUTO_INCREMENT for table `app_news_messages`
--
ALTER TABLE `app_news_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `app_updates`
--
ALTER TABLE `app_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `proxy_lists`
--
ALTER TABLE `proxy_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=617;

--
-- AUTO_INCREMENT for table `proxy_providers`
--
ALTER TABLE `proxy_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126039;

--
-- AUTO_INCREMENT for table `trial_device_claims`
--
ALTER TABLE `trial_device_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `trial_verifications`
--
ALTER TABLE `trial_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `wallet_addresses`
--
ALTER TABLE `wallet_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208833;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD CONSTRAINT `user_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
