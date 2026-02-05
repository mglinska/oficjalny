-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Czas generowania: 25 Wrz 2022, 23:37
-- Wersja serwera: 8.0.30-0ubuntu0.20.04.2
-- Wersja PHP: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Baza danych: `systemegzaminacyjny`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `nauczyciel`
--

CREATE TABLE `nauczyciel` (
  `id_nauczyciel` int NOT NULL,
  `login_nauczyciel` varchar(7) NOT NULL,
  `haslo` varchar(16) NOT NULL,
  `imie` varchar(30) NOT NULL,
  `nazwisko` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Zrzut danych tabeli `nauczyciel`
--

INSERT INTO `nauczyciel` (`id_nauczyciel`, `login_nauczyciel`, `haslo`, `imie`, `nazwisko`) VALUES
(1, 'kjan', '12345', 'Jan', 'Kowalski');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `odpowiedz`
--

CREATE TABLE `odpowiedz` (
  `id_odpowiedz` int NOT NULL,
  `id_pytanie` int NOT NULL,
  `tresc` varchar(500) DEFAULT NULL,
  `poprawna` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `odpowiedz_zadane_pytanie`
--

CREATE TABLE `odpowiedz_zadane_pytanie` (
  `id_odpowiedz` int NOT NULL,
  `id_zadane_pytanie` int NOT NULL,
  `kolejnosc` int NOT NULL,
  `zaznaczona` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `proba`
--

CREATE TABLE `proba` (
  `id_proba` int NOT NULL,
  `id_student` int NOT NULL,
  `id_test` int NOT NULL,
  `ocena` int DEFAULT NULL,
  `zaliczony` tinyint DEFAULT NULL,
  `data_rozpoczecia` datetime DEFAULT NULL,
  `data_zakonczenia` datetime DEFAULT NULL,
  `czas_rozwiazania` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pytanie`
--

CREATE TABLE `pytanie` (
  `id_pytanie` int NOT NULL,
  `id_test` int NOT NULL,
  `tresc` varchar(500) DEFAULT NULL,
  `typ` varchar(45) NOT NULL,
  `maksymalna_ilosc_punktow` int NOT NULL,
  `kolejnosc` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `student`
--

CREATE TABLE `student` (
  `id_student` int NOT NULL,
  `login_student` varchar(7) NOT NULL,
  `haslo` varchar(16) NOT NULL,
  `imie` varchar(30) NOT NULL,
  `nazwisko` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Zrzut danych tabeli `student`
--

INSERT INTO `student` (`id_student`, `login_student`, `haslo`, `imie`, `nazwisko`) VALUES
(1, 'cf46484', '12345', 'Francesco', 'Carvelli');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `student_test`
--

CREATE TABLE `student_test` (
  `id_student` int NOT NULL,
  `id_test` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `test`
--

CREATE TABLE `test` (
  `id_test` int NOT NULL,
  `id_nauczyciel` int NOT NULL,
  `nazwa` varchar(100) DEFAULT NULL,
  `data_utworzenia` datetime NOT NULL,
  `aktywny_od` datetime DEFAULT NULL,
  `aktywny_do` datetime DEFAULT NULL,
  `ilosc_czasu` int NOT NULL,
  `prog` varchar(30) DEFAULT NULL,
  `sposob_oceniania_pytan` varchar(500) NOT NULL,
  `losowa_kolejnosc_pytan` tinyint NOT NULL,
  `losowa_kolejnosc_odpowiedzi` tinyint NOT NULL,
  `maksymalna_ilosc_prob` int DEFAULT NULL,
  `sposob_oceniania_prob` varchar(50) NOT NULL,
  `cofanie` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zadane_pytanie`
--

CREATE TABLE `zadane_pytanie` (
  `id_zadane_pytanie` int NOT NULL,
  `id_proba` int NOT NULL,
  `id_pytanie` int NOT NULL,
  `kolejnosc` int NOT NULL,
  `ilosc_punktow` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `nauczyciel`
--
ALTER TABLE `nauczyciel`
  ADD PRIMARY KEY (`id_nauczyciel`);

--
-- Indeksy dla tabeli `odpowiedz`
--
ALTER TABLE `odpowiedz`
  ADD PRIMARY KEY (`id_odpowiedz`),
  ADD KEY `odpowiedz_ibfk_1` (`id_pytanie`);

--
-- Indeksy dla tabeli `odpowiedz_zadane_pytanie`
--
ALTER TABLE `odpowiedz_zadane_pytanie`
  ADD PRIMARY KEY (`id_odpowiedz`,`id_zadane_pytanie`),
  ADD KEY `odpowiedz_zadane_pytanie_ibfk_1` (`id_zadane_pytanie`);

--
-- Indeksy dla tabeli `proba`
--
ALTER TABLE `proba`
  ADD PRIMARY KEY (`id_proba`),
  ADD KEY `proba_ibfk_1` (`id_student`),
  ADD KEY `proba_ibfk_2` (`id_test`);

--
-- Indeksy dla tabeli `pytanie`
--
ALTER TABLE `pytanie`
  ADD PRIMARY KEY (`id_pytanie`);

--
-- Indeksy dla tabeli `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id_student`);

--
-- Indeksy dla tabeli `student_test`
--
ALTER TABLE `student_test`
  ADD PRIMARY KEY (`id_student`,`id_test`),
  ADD KEY `student_test_ibfk_2` (`id_test`);

--
-- Indeksy dla tabeli `test`
--
ALTER TABLE `test`
  ADD PRIMARY KEY (`id_test`),
  ADD KEY `test_ibfk_1` (`id_nauczyciel`);

--
-- Indeksy dla tabeli `zadane_pytanie`
--
ALTER TABLE `zadane_pytanie`
  ADD PRIMARY KEY (`id_zadane_pytanie`),
  ADD KEY `zadane_pytanie_ibfk_1` (`id_proba`),
  ADD KEY `zadane_pytanie_ibfk_2` (`id_pytanie`);

--
-- AUTO_INCREMENT dla tabel zrzutów
--

--
-- AUTO_INCREMENT dla tabeli `nauczyciel`
--
ALTER TABLE `nauczyciel`
  MODIFY `id_nauczyciel` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT dla tabeli `odpowiedz`
--
ALTER TABLE `odpowiedz`
  MODIFY `id_odpowiedz` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT dla tabeli `proba`
--
ALTER TABLE `proba`
  MODIFY `id_proba` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT dla tabeli `pytanie`
--
ALTER TABLE `pytanie`
  MODIFY `id_pytanie` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT dla tabeli `student`
--
ALTER TABLE `student`
  MODIFY `id_student` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT dla tabeli `test`
--
ALTER TABLE `test`
  MODIFY `id_test` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT dla tabeli `zadane_pytanie`
--
ALTER TABLE `zadane_pytanie`
  MODIFY `id_zadane_pytanie` int NOT NULL AUTO_INCREMENT;

--
-- Ograniczenia dla zrzutów tabel
--

--
-- Ograniczenia dla tabeli `odpowiedz`
--
ALTER TABLE `odpowiedz`
  ADD CONSTRAINT `odpowiedz_ibfk_1` FOREIGN KEY (`id_pytanie`) REFERENCES `pytanie` (`id_pytanie`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `odpowiedz_zadane_pytanie`
--
ALTER TABLE `odpowiedz_zadane_pytanie`
  ADD CONSTRAINT `odpowiedz_zadane_pytanie_ibfk_1` FOREIGN KEY (`id_zadane_pytanie`) REFERENCES `zadane_pytanie` (`id_zadane_pytanie`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `odpowiedz_zadane_pytanie_ibfk_2` FOREIGN KEY (`id_odpowiedz`) REFERENCES `odpowiedz` (`id_odpowiedz`) ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `proba`
--
ALTER TABLE `proba`
  ADD CONSTRAINT `proba_ibfk_1` FOREIGN KEY (`id_student`) REFERENCES `student` (`id_student`) ON UPDATE CASCADE,
  ADD CONSTRAINT `proba_ibfk_2` FOREIGN KEY (`id_test`) REFERENCES `test` (`id_test`) ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `student_test`
--
ALTER TABLE `student_test`
  ADD CONSTRAINT `student_test_ibfk_1` FOREIGN KEY (`id_student`) REFERENCES `student` (`id_student`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_test_ibfk_2` FOREIGN KEY (`id_test`) REFERENCES `test` (`id_test`) ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `test`
--
ALTER TABLE `test`
  ADD CONSTRAINT `test_ibfk_1` FOREIGN KEY (`id_nauczyciel`) REFERENCES `nauczyciel` (`id_nauczyciel`) ON UPDATE CASCADE;

--
-- Ograniczenia dla tabeli `zadane_pytanie`
--
ALTER TABLE `zadane_pytanie`
  ADD CONSTRAINT `zadane_pytanie_ibfk_1` FOREIGN KEY (`id_proba`) REFERENCES `proba` (`id_proba`) ON UPDATE CASCADE,
  ADD CONSTRAINT `zadane_pytanie_ibfk_2` FOREIGN KEY (`id_pytanie`) REFERENCES `pytanie` (`id_pytanie`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
