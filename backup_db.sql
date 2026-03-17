-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: db
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mensajes`
--

DROP TABLE IF EXISTS `mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  `archivo` varchar(255) DEFAULT NULL,
  `tipo` enum('imagen','video') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mensajes`
--

LOCK TABLES `mensajes` WRITE;
/*!40000 ALTER TABLE `mensajes` DISABLE KEYS */;
INSERT INTO `mensajes` VALUES (2,2,'hola','2026-02-18 18:24:09',NULL,NULL),(3,2,'gnomo','2026-02-18 18:24:26','uploads/699603da35d4d_giphy.gif','imagen'),(4,5,'Mensaje de prueba desde Postman','2026-02-18 18:47:05',NULL,NULL),(5,2,'jeje\r\n','2026-02-18 19:03:36',NULL,NULL),(6,2,'e','2026-02-18 19:03:41',NULL,NULL),(7,2,'a','2026-02-18 19:03:45','uploads/69960d115f9c7_images.jpg','imagen'),(8,2,'a','2026-02-18 19:04:01',NULL,NULL),(9,2,'a','2026-02-18 19:04:06','uploads/69960d26b73db_2.gif','imagen'),(10,6,'aaa','2026-03-04 18:07:49',NULL,NULL),(11,7,'hola juan, buen trabajo niño!!!!!! me gusta :)\r\n','2026-03-04 18:10:52',NULL,NULL),(12,8,'a','2026-03-09 18:20:47',NULL,NULL);
/*!40000 ALTER TABLE `mensajes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nick` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','user') NOT NULL DEFAULT 'user',
  `api_token` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (2,'Juan','$2y$10$m8pqaTuwsnBGOPURXBsR/OwdQuVKABSykBYsWN9B7/YMDhsma0GlO','user',NULL),(3,'trape','$2y$10$9T3JlN6peTHkzsYzUYZhFu2Etkqm5tktRIGqTWjXNlGwxu6FTNl3a','user',NULL),(4,'trape2','$2y$10$41aVj.0qEyBbTOqIp6U.t.djR1PEPs3TtH9dWvbWFdvjYeiBONXnu','user',NULL),(5,'usuario_test','$2y$10$GOJ3PG0z8QGRrjfntiNYeeRwnWizNuggxqi3yp1d1uY.blWN2yv1e','user',NULL),(6,'test','$2y$10$Opz1KfG8u5lUv6q9ApspEOOT2yviOpFUz/nJPbmqZuwujroJDDB2y','user',NULL),(7,'prueba','$2y$10$wm3MbV2xjaSwrAQQemDhT.Z7aRNAs3MJaS9vCr1gAmmnGoepGDwa2','user',NULL),(8,'admin','$2y$10$O4tHrdbNZjFT0rm1UIIqku9Y07CKbSV5FW8mM9C3sultn.d85.fEO','admin','1234567890abcdef1234567890abcdef'),(9,'MANOLO','$2y$10$lfDz37AJu1GKNXxoJ32QX.82xsPiQZgGFvJmmvhB0Z31kBu.TF71K','user','8781453151789399e1cba2b6cb59a73d28fad90d0a4338129aec1d5afb246219');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-17 16:22:07
