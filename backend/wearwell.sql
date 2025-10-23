-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 11:19 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wearwell`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `size` varchar(5) DEFAULT 'M'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `added_at`, `size`) VALUES
(37, 7, 35, 2, '2025-10-23 07:34:48', 'M');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Celana'),
(5, 'Jacket'),
(3, 'Jersey'),
(4, 'Kaos'),
(2, 'Sweater');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_orders` int(11) NOT NULL,
  `id_users` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Processing','Shipped','Completed','Canceled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_orders`, `id_users`, `total`, `status`, `created_at`) VALUES
(9, 7, '899000.00', 'Shipped', '2025-10-22 14:34:41'),
(10, 7, '679000.00', 'Processing', '2025-10-22 15:49:28'),
(11, 7, '475000.00', 'Pending', '2025-10-22 17:20:25'),
(12, 7, '309000.00', 'Processing', '2025-10-23 06:08:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id_items` int(11) NOT NULL,
  `id_orders` int(11) DEFAULT NULL,
  `id_products` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id_items`, `id_orders`, `id_products`, `quantity`, `price`) VALUES
(8, 9, 9, 1, '899000.00'),
(9, 10, 21, 1, '679000.00'),
(10, 11, 43, 1, '475000.00'),
(11, 12, 30, 1, '309000.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `size` varchar(5) DEFAULT 'M',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `price`, `stock`, `image`, `created_at`, `size`, `description`) VALUES
(2, 12, 5, 'Red Star Bomber Jacket', '829000.00', 10, 'f2.jpg', '2025-10-21 16:35:25', 'M', 'This bomber screams confidence. The standout red star design adds a rebellious twist to a clean black satin base. Lightweight, bold, and easy to style — it’s the go-to piece for nights out or chill street looks.'),
(9, 12, 5, 'DRGS Varsity Leather Jacket', '899000.00', 12, 'f3.jpg', '2025-10-21 16:38:10', 'M', 'Step up your street vibe with the DRGS varsity jacket — a bold mix of black-and-white leather featuring graphic lettering and retro varsity patches. It’s got that perfect “downtown energy” — where classic college style meets edgy streetwear.'),
(11, 12, 5, 'I Don’t Belong Alone Varsity Jacket', '869000.00', 15, 'f4.jpg', '2025-10-21 16:41:50', 'M', 'Soft beige tones meet bold black text in this oversized varsity piece. It’s a unisex favorite that balances cozy and cool — ideal for layering over hoodies or cropped tops for that vintage Y2K touch.'),
(12, 12, 5, 'Rebels Oversized Baseball Jacket', '899000.00', 14, 'f5.jpg', '2025-10-21 16:44:17', 'M', 'Retro meets rebellion — this black satin baseball jacket with silver embroidery gives major throwback energy. It’s got the perfect relaxed silhouette that fits any gender, mood, or playlist.'),
(13, 12, 1, 'Spiderweb Denim Shorts', '519000.00', 16, 'f8.jpg', '2025-10-21 16:54:19', 'M', 'Dare to stand out. These dark denim shorts feature subtle spiderweb embroidery, serving gothic grunge realness while staying comfy. A top pick for edgy summer looks.'),
(14, 12, 1, 'Y2K Wide-Leg Cargo Jeans', '689000.00', 25, 'f6.jpg', '2025-10-21 16:56:02', 'M', 'Heavy denim, wide legs, and cargo pockets — the ultimate Y2K revival piece. The washed grey tone adds a rugged feel, while the relaxed fit keeps it effortlessly cool for everyday wear.'),
(15, 12, 1, 'Dark Star Cargo Jeans', '689000.00', 20, 'f12.jpg', '2025-10-21 16:57:13', 'M', 'These star-patched cargos are a full-on grunge dream. The structured cut gives them that utility edge, while the faded black denim adds serious retro flavor — perfect for streetstyle shoots.'),
(16, 12, 1, 'Washed Utility Denim Cargo Pants', '639000.00', 20, 'f15.jpg', '2025-10-21 16:58:16', 'M', 'Washed-out and wild — these distressed cargos bring major early-2000s nostalgia. With deep side pockets and loose Y2K proportions, they’re the ultimate mix of function and statement.'),
(17, 12, 1, 'Deconstructed Denim Shorts', '599000.00', 12, 'f7.jpg', '2025-10-21 16:59:10', 'M', 'Go bold with these dark-wash denim shorts featuring raw hems, strap details, and cargo-inspired pockets. The edgy silhouette screams confidence while keeping it practical and comfy. Ideal for anyone who wants to turn a simple streetwear look into a statement.'),
(18, 12, 1, '\'Brown Bliss Corduroy Pants', '645000.00', 10, 'f23.jpg', '2025-10-21 17:00:19', 'M', NULL),
(19, 12, 1, 'MarbleFade Denim Pants', '645000.00', 10, 'f37.jpg', '2025-10-21 17:01:35', 'M', 'Statement jeans done right — these marble-wash denims bring a bold, artsy edge to your outfit. With a relaxed straight fit and washed texture, they give any look a confident streetwear twist.'),
(20, 12, 1, 'SoftSky Vintage Jeans', '615000.00', 19, 'f29.jpg', '2025-10-21 17:02:23', 'M', 'Classic light-wash jeans with gentle distressing for that lived-in feel. Comfortable, versatile, and totally unisex — perfect for casual days, coffee runs, or pairing with graphic tees and hoodies.'),
(21, 12, 1, 'Midnight Levis Classic Denim', '679000.00', 17, 'f26.jpg', '2025-10-21 17:03:16', 'M', 'A deep indigo take on the timeless Levis silhouette. Straight-leg, structured, and effortlessly stylish — these jeans are made to last and made to match literally everything in your wardrobe.'),
(22, 12, 1, 'BlueFade Relaxed Jeans', '629000.00', 21, 'f28.jpg', '2025-10-21 17:04:42', 'M', 'The ultimate 2000s-inspired pair — soft denim with a relaxed, wide-leg fit and vintage wash. Perfect for baggy streetwear looks or a cozy, gender-free casual fit.'),
(23, 12, 4, 'Vintage Garfield Graphic Tee', '299000.00', 30, 'f27.jpg', '2025-10-21 17:05:57', 'M', 'Bring the nostalgia. This oversized Garfield tee gives laid-back humor and vintage pop culture vibes — perfect for casual fits or street layering.'),
(24, 12, 4, 'Cats Print Cream Tee', '27500.00', 28, 'f9.jpg', '2025-10-21 17:06:53', 'M', 'Soft, playful, and artsy — this tee features a hand-drawn-style cat illustration. The minimalist design gives a calm, indie aesthetic that pairs effortlessly with jeans or cargo pants.'),
(25, 12, 5, 'UrbanEdge Hooded Leather Jacket', '105000.00', 8, 'f39.jpg', '2025-10-21 17:08:27', 'M', 'Level up your streetwear game with this black leather jacket featuring a soft inner hood. It’s the perfect mix of edgy and cozy — giving that “city rebel” look without sacrificing comfort. The oversized cut makes it easy to layer over hoodies or tees, keeping your outfit effortlessly on point all season long.'),
(26, 12, 5, 'Threshold Oversized Leather Jacket', '107500.00', 6, 'f34.jpg', '2025-10-21 17:11:00', 'M', 'This jet-black leather jacket redefines oversized cool. With a relaxed silhouette and soft matte texture, it’s built for layering and attitude. Pair it with tees, cargos, or even skirts — it elevates anything instantly.'),
(27, 12, 5, 'CrimsonRetro Leather Jacket', '995000.00', 9, 'f33.jpg', '2025-10-21 17:12:23', 'M', 'Bold, confident, and a little rebellious — this dark red leather jacket brings instant main-character energy. The vintage finish adds that perfect worn-in look that feels straight out of a 90s movie.'),
(28, 12, 5, 'NightDrive Zip Jacket', '785000.00', 13, 'f24.jpg', '2025-10-21 17:13:23', 'M', 'Stay sleek with this all-black zip-up featuring subtle white line details. Lightweight and edgy, it’s a go-to for cool-weather fits or late-night adventures. Minimal, genderless, and always in style.'),
(29, 12, 4, 'Cosmic Vibe Graphic Tee', '289000.00', 25, 'f14.jpg', '2025-10-21 18:34:21', 'M', 'Step into a retro daydream with this blue oversized tee featuring a dreamy cosmic print. The soft cotton fabric and relaxed fit make it your go-to for chill days or creative hangouts. Pair it with loose denim or cargos for that effortless Y2K aesthetic.'),
(30, 12, 4, 'Arigato Street Graphic Tee', '309000.00', 18, 'f22.jpg', '2025-10-21 18:35:17', 'M', 'Say hello to street style with a Japanese twist! This beige tee features a vintage-inspired “Arigato” print that brings Tokyo vibes wherever you go. It’s breathable, slightly oversized, and made to keep you looking laid-back yet stylish — perfect for everyday fits or casual hangs.'),
(31, 12, 4, 'Lucky Star Vintage Tee', '279000.00', 22, 'f36.jpg', '2025-10-21 18:36:14', 'M', 'Bring the Y2K energy with this navy baby tee featuring a bold star print. Hand-screened for that unique, handmade vibe, this piece mixes vintage charm with modern coquette flair. Perfect for layering or rocking solo with your favorite jeans.'),
(32, 12, 4, 'DarkRose Gothic Graphic Tee', '299000.00', 20, 'f38.jpg', '2025-10-21 18:37:00', 'M', 'This oversized grunge tee blends edgy art with a vintage wash finish. The rose-and-skeleton design screams rebellion with a hint of romance. Crafted for comfort and expression, it’s the ultimate unisex piece for late-night hangs or indie gigs.'),
(33, 12, 3, 'Extra Cash Jersey Shirt', '319000.00', 15, 'f18.jpg', '2025-10-21 18:37:51', 'M', 'Sporty meets street in this sleek black jersey with bold white detailing. Designed for comfort and confidence, it gives off major underground fashion energy. Pair it with cargos or denim for an easy, athletic street look.'),
(34, 12, 3, 'RetroWave Oversized Tee', '289000.00', 27, 'f31.jpg', '2025-10-21 18:38:54', 'M', 'A throwback to 90s street style, this navy-and-white contrast tee from Aelfric Eden nails that vintage summer aesthetic. Lightweight, breathable, and oversized — it’s the kind of shirt that looks effortlessly cool on anyone.'),
(35, 12, 3, 'CaliDream Jersey Tee', '305000.00', 16, 'f16.jpg', '2025-10-21 18:39:52', 'M', 'Stay sporty and sweet with this pink-and-white jersey tee that gives off major California summer vibes. It’s light, comfy, and stylishly oversized — the perfect piece for sunny days, skate sessions, or just looking effortlessly cute.'),
(36, 12, 3, 'FairFocus Polo Knit', '349000.00', 18, 'f21.jpg', '2025-10-21 18:40:55', 'M', 'A perfect mix of classy and street — this green-and-white polo knit features gothic lettering and butterfly prints for that artsy preppy vibe. Lightweight and breathable, it’s perfect for casual hangouts or turning heads at a chill party.'),
(37, 12, 2, 'DinoVibe Knit Sweater', '355000.00', 10, 'f32.jpg', '2025-10-21 18:41:56', 'M', 'Bring back the retro vibes with this cozy green knit covered in playful dinosaur patterns. Made from soft acrylic fabric, it keeps you warm and comfy all day. The oversized unisex fit makes it the perfect pick for chill streetwear looks or weekend hangouts.'),
(38, 12, 2, 'CatRetro Knit Crew', '469000.00', 12, 'f10.jpg', '2025-10-21 18:42:50', 'M', 'A vintage twist for your everyday look — this navy knit crew features a classic cat graphic with retro-style text. Soft to the touch and perfectly warm, it’s a must-have for anyone who loves laid-back, nostalgic fashion with a modern edge.'),
(39, 12, 2, 'StarBurst Oversized Knit', '485000.00', 8, 'f35.jpg', '2025-10-21 18:44:04', 'M', 'Shine bright with the StarBurst Knit! This navy sweater with a bold star graphic is a total statement piece. Its loose Y2K-inspired fit makes it perfect for layering or pairing with baggy jeans for that effortless street-style look.'),
(40, 12, 2, 'LoveCore Patch Sweater', '499000.00', 14, 'f17.jpg', '2025-10-21 18:44:59', 'M', 'Fall in love with the LoveCore aesthetic! Featuring bold heart patches and warm cream-red tones, this knit screams cozy romance. Designed for all genders, it’s the ultimate choice for soft, comfy days or matching couple outfits.'),
(41, 12, 2, 'SpacePunk Astro Kni', '515900.00', 9, 'f30.jpg', '2025-10-21 18:46:01', 'M', 'Step into orbit with this black-and-white sweater covered in astronaut and star motifs. Its grunge-meets-futuristic vibe makes it a standout piece for edgy fashion lovers. Comfy, oversized, and effortlessly cool — it’s made for dreamers and rebels.'),
(42, 12, 2, 'NightCat Chill Sweatshirt', '465000.00', 20, 'f20.jpg', '2025-10-21 18:46:54', 'M', 'Keep it lowkey and chill with this olive green sweatshirt featuring a minimal black cat print. It’s soft, comfy, and has that effortlessly cool aesthetic for casual days, coffee runs, or staying cozy at home.'),
(43, 12, 2, 'WowDuck Cozy Knit', '475000.00', 15, 'f11.jpg', '2025-10-21 18:47:54', 'M', 'Make people smile with this adorable blue knit featuring a big “WOW” duck graphic. It’s oversized, cozy, and super fun — the kind of sweater that turns a lazy day outfit into a total vibe. Perfect for anyone who loves quirky, comfy fashion.'),
(44, 12, 2, 'WaveCore Vintage Knit', '489000.00', 11, 'f25.jpg', '2025-10-21 18:48:32', 'M', 'Ride the vintage wave with this cream knit sweater decorated with abstract wave patterns. The warm tones and relaxed fit give it a grunge aesthetic that’s both artistic and timeless. It’s your go-to piece for cozy, moody days.'),
(45, 12, 2, 'DuckRush Street Knit', '479000.00', 13, 'f13.jpg', '2025-10-21 18:49:10', 'M', 'A crowd favorite for playful souls — this navy sweater covered in cartoon ducks is both cute and stylish. The loose unisex fit gives off a comfy streetwear energy, perfect for those who don’t take fashion too seriously but still want to look good'),
(46, 12, 2, 'ButterGrunge Y2K Sweater', '525000.00', 7, 'f19.jpg', '2025-10-21 18:49:55', 'M', 'Add a touch of dreamy rebellion with this lavender knit featuring a large butterfly print. The mix of soft Y2K tones and grunge edge makes it ideal for expressing your creative side. Style it with baggy pants or a mini skirt — either way, it slaps.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','seller') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(7, 'febi', 'febiliyanti@gmail.com', '$2y$10$6uyL3UMTTmKC9aaJ.UL6oOGHq6uhDN0ce0DWqWsn8ZAiHbAb7yE1S', 'user', '2025-10-21 15:08:27'),
(12, 'seller01', 'seller01@example.com', '$2y$10$k27mWxNo9ZUOxbZdk2KbleWzNdbJ.0Qbw3SSlcQ2O.OMTXmane84y', 'seller', '2025-10-21 15:44:43'),
(13, 'user', 'user@gmail.com', '$2y$10$vN93jnRi8n4KRkLLhjJwaumVdTYTP5ETw/WOfBA1wAfvU6BnRDwCy', 'user', '2025-10-22 17:32:49'),
(14, 'Nathan', 'nathan.seller@mail.com', '$2y$10$k27mWxNo9ZUOxbZdk2KbleWzNdbJ.0Qbw3SSlcQ2O.OMTXmane84y', 'seller', '2025-10-22 17:46:27'),
(15, 'Widya', 'widya.seller@mail.com', '$2y$10$k27mWxNo9ZUOxbZdk2KbleWzNdbJ.0Qbw3SSlcQ2O.OMTXmane84y', 'seller', '2025-10-22 17:46:27'),
(16, 'Angel', 'angel.seller@mail.com', '$2y$10$k27mWxNo9ZUOxbZdk2KbleWzNdbJ.0Qbw3SSlcQ2O.OMTXmane84y', 'seller', '2025-10-22 17:46:27'),
(17, 'Anjel', 'anjelia.seller@mail.com', '$2y$10$k27mWxNo9ZUOxbZdk2KbleWzNdbJ.0Qbw3SSlcQ2O.OMTXmane84y', 'seller', '2025-10-22 17:46:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_orders`),
  ADD KEY `id_users` (`id_users`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id_items`),
  ADD KEY `id_orders` (`id_orders`),
  ADD KEY `id_products` (`id_products`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_orders` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id_items` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`id_orders`) REFERENCES `orders` (`id_orders`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
