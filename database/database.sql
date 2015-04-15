-- phpMyAdmin SQL Dump
-- version 4.2.12deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 15, 2015 at 08:04 PM
-- Server version: 5.6.19-1~dotdeb.1
-- PHP Version: 5.5.20-1~dotdeb.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `njam`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `dodaj_konobara`(uname varchar(32), pass varchar(32), rest int)
begin 

declare ajdi int;
insert into 
    korisnik (username,password,vrsta)
    values
    (uname,
     pass,
     'konobar'
    );
    
    set ajdi = (select id from korisnik where username = uname);
    
    insert into konobar (id_konobar,id_restoran) values (ajdi,rest);
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dodaj_restoran`(IN `uname` VARCHAR(32), IN `pass` VARCHAR(32), IN `ime` VARCHAR(64), IN `adr` VARCHAR(64), IN `url` VARCHAR(256), IN `lok` VARCHAR(64))
    NO SQL
begin 

declare ajdi int;
insert into 
    korisnik (username,password,vrsta)
    values
    (uname,
     pass,
     'restoran'
    );
    
    set ajdi = (select id from korisnik where username = uname);
    
    insert into restoran (id_restoran,ime_restoran,adresa,url_slike,lokacija) values (ajdi,ime,adr,url,lok);
end$$

CREATE DEFINER=`root`@`%` PROCEDURE `gost__dohvati_ocjene`(p_id INT)
BEGIN
    DECLARE max INT;
    SELECT max(suma)
    INTO max
    FROM (SELECT count(recenzija.id_recenzija) AS suma
          FROM recenzija
          WHERE id_autor = p_id
          GROUP BY ocjena) AS s;
    SELECT
      (SELECT round(avg(ocjena),1)
       FROM recenzija
       WHERE id_autor = p_id)                AS ukupna_ocjena,
      (SELECT count(ocjena)
       FROM recenzija
       WHERE id_autor = p_id)                AS broj_ocjena,
      (SELECT round(count(ocjena) / max * 100, 1) AS broj_ocjena
       FROM recenzija
       WHERE id_autor = p_id AND ocjena = 1) AS udio_1,
      (SELECT round(count(ocjena) / max * 100, 1) AS broj_ocjena
       FROM recenzija
       WHERE id_autor = p_id AND ocjena = 2) AS udio_2,
      (SELECT round(count(ocjena) / max * 100, 1) AS broj_ocjena
       FROM recenzija
       WHERE id_autor = p_id AND ocjena = 3) AS udio_3,
      (SELECT round(count(ocjena) / max * 100, 1) AS broj_ocjena
       FROM recenzija
       WHERE id_autor = p_id AND ocjena = 4) AS udio_4,
      (SELECT round(count(ocjena) / max * 100, 1) AS broj_ocjena
       FROM recenzija
       WHERE id_autor = p_id AND ocjena = 5) AS udio_5;

  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `gost__dohvati_recenzije`(p_id int)
begin
    select recenzija.tekst,
      date(recenzija.vremenska_oznaka) as datum,
      ime_restoran as nadimak,
      concat(ocjena,'.0') as ocjena from recenzija left join ugostitelj on ugostitelj.id_ugostitelj = recenzija.id_ugostitelj where id_autor = p_id;

  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `gost__dohvati_rezervacije`(p_id_gost INT)
BEGIN
    delete from rezervacija where rezervacija.id_gost = p_id_gost and rezervacija.broj_osoba<0;
    SELECT
      unix_timestamp(rezervacija.vrijeme_kraj) as vrijeme_kraj,
      unix_timestamp(rezervacija.vrijeme_pocetak) as vrijeme_pocetak,
      rezervacija.id_rezervacija,
      rezervacija.broj_osoba,
      ugostitelj.ime_restoran,
      ugostitelj.adresa
    FROM rezervacija
      JOIN ugostitelj ON ugostitelj.id_ugostitelj = rezervacija.id_ugostitelj
    WHERE rezervacija.id_gost = p_id_gost;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `gost__registriraj`(p_id int,  p_ime varchar(64),p_email varchar(256))
BEGIN
    insert into gost(id_gost, ime_prezime, email)
    VALUES (p_id,p_ime,p_email);
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `gost__ucitaj`(p_gost int)
begin 
    select * from gost where gost.id_gost = p_gost;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `inspekcija__dohvati_ocjene`(p_id INT)
BEGIN
    CALL gost__dohvati_ocjene(p_id);
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `inspekcija__dohvati_recenzije`(p_id INT)
BEGIN
    CALL gost__dohvati_recenzije(p_id);
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__dodaj_jelo`(p_ugostitelj INT, p_naziv VARCHAR(128), p_vrsta INT, p_cijena DECIMAL(6, 2))
BEGIN
    INSERT INTO jelo (
      id_ugostitelj
      , ime_jela
      , cijena_jela
      , id_vrsta_jela
    )
    VALUES (
      p_ugostitelj
      , p_naziv
      , p_cijena
      , p_vrsta
    );
    select LAST_INSERT_ID() as id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__dodaj_ponuda_jela`(p_ugostitelj INT, p_naziv VARCHAR(64))
BEGIN
    INSERT INTO ponuda_jela (naziv_ponuda, id_ugostitelj)
    VALUES (p_naziv, p_ugostitelj);
    select LAST_INSERT_ID() as id;

  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__ucitaj_jela`(p_id_ugostitelj int)
begin 
      select 
        jelo.*
      from jelo where jelo.id_ugostitelj = p_id_ugostitelj;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__ucitaj_ponude_jela`(p_id_ugostitelj INT)
BEGIN
    SELECT
      distinct ponuda_jela.*
    FROM ponuda_jela
      LEFT JOIN ponuda ON ponuda.id_ponude_jela = ponuda_jela.id_ponuda
    WHERE ponuda_jela.id_ugostitelj = p_id_ugostitelj;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__ucitaj_vrste_jela`()
BEGIN
    SELECT
      *
    FROM vrsta_jela;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__ukloni_jelo`(p_id int)
begin 
    delete from jelo where jelo.id_jelo = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__ukloni_ponuda_jela`(p_id int)
begin
    delete from ponuda_jela where ponuda_jela.id_ponuda = p_id;
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__uredi_jelo`(p_id INT, p_naziv VARCHAR(128), p_cijena DECIMAL(6, 2))
BEGIN
    UPDATE jelo
    SET
      jelo.ime_jela      = p_naziv,
      jelo.cijena_jela   = p_cijena
    WHERE
      jelo.id_jelo = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `jelovnik__uredi_ponuda_jela`(p_id_ponude int, p_naziv varchar(64))
begin 
    update ponuda_jela
      set 
        ponuda_jela.naziv_ponuda = p_naziv
    where 
      ponuda_jela.id_ponuda = p_id_ponude;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `konobar__registriraj`(p_id int,p_id_ugostitelj int)
begin 
    insert into konobar(id_konobar, id_ugostitelj) values (p_id,p_id_ugostitelj);
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `konobar__ucitaj`(p_id int)
begin 
    select * from konobar where konobar.id_konobar = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `konobar__ukloni`(p_id_konobar INT, p_id_ugostitelj INT)
BEGIN
    IF ((SELECT count(id_konobar)
         FROM konobar
         WHERE id_ugostitelj = p_id_ugostitelj) <= 1)
    THEN
      BEGIN
        SIGNAL SQLSTATE 'ERROR'
        SET MESSAGE_TEXT ='Objekt mora imati bar jednog konobara', MYSQL_ERRNO = '1356';
      END;
    ELSE
      BEGIN
        DELETE FROM korisnik
        WHERE korisnik.id = p_id_konobar AND id IN (SELECT id_konobar
                                                    FROM konobar
                                                    WHERE id_ugostitelj = p_id_ugostitelj);
      END;
    END IF;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `korisnik__registriraj`(
    p_korisnicko_ime VARCHAR(32)
  , p_lozinka        VARCHAR(32)
  , p_id_vrsta       INT
)
proc: BEGIN
    DECLARE tmp INT;

    SELECT
      id
    INTO tmp
    FROM korisnik
    WHERE korisnicko_ime = p_korisnicko_ime;

    IF (tmp IS NOT NULL)
    THEN
      SELECT
        'Korisni&#269;ko ime ve&#263; postoji' AS status;
      LEAVE proc;
    END IF;

    INSERT INTO korisnik (
      korisnicko_ime,
      lozinka,
      id_vrsta
    ) VALUES (
      p_korisnicko_ime,
      p_lozinka,
      p_id_vrsta
    );
    
    select LAST_INSERT_ID() as id, 'OK' as status;  
    
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `korisnik__ucitaj`(p_id int)
begin
    SELECT korisnicko_ime,id_vrsta FROM korisnik WHERE korisnik.id = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `nabava__dodaj_namirnica`(p_id_nabava int, p_id_namirnica int, p_kolicina int)
begin 
  #delete from nabava_namirnice where id_nabava = p_id_nabava and id_namirnica = p_id_namirnica;
    insert into nabava_namirnice(id_nabava, id_namirnica, kolicina) values (p_id_nabava,p_id_namirnica,p_kolicina);
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `nabava__dohvati_sve_za_dobavljaca`(p_id int)
begin
    select nabava.id_nabava from nabava where nabava.id_dobavljac =p_id and status=0;
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `nabava__dohvati_sve_za_ugostitelja`(p_id int)
begin 
    select nabava.id_nabava from nabava where nabava.id_ugostitelj =p_id and status in (0,1,2);
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `nabava__kreiraj`(p_id_ugostitelj INT)
BEGIN
    INSERT INTO nabava (id_ugostitelj, id_dobavljac) VALUES (p_id_ugostitelj, (
      SELECT id_dobavljac
      FROM ugostitelj
      WHERE ugostitelj.id_ugostitelj =
            p_id_ugostitelj
    ));
    SELECT LAST_INSERT_ID() AS id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `nabava__ucitaj_namirnice`(p_id INT)
BEGIN
    SELECT
      namirnica.id_namirnica,
      namirnica.id_ugostitelj,
      namirnica.naziv,
      nabava_namirnice.kolicina
      
    FROM nabava_namirnice left join namirnica on namirnica.id_namirnica = nabava_namirnice.id_namirnica
    WHERE nabava_namirnice.id_nabava = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `namirnica__ucitaj`(p_id int)
begin 
    select * from namirnica where namirnica.id_namirnica = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `odabran_jelovnik__dodaj`(p_id_rezervacija int, p_id_jelo int, p_kolicina int)
begin
    insert into narudzba(id_rezervacija,id_jelo, kolicina)
    values (p_id_rezervacija,p_id_jelo,p_kolicina);
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `odabran_jelovnik__oduzmi`(p_id_rezervacija int, p_id_jelo int)
begin
    delete from narudzba 
    where narudzba.id_jelo = p_id_jelo and narudzba.id_rezervacija = p_id_rezervacija;
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `odabran_jelovnik__ucitaj`(p_id int)
begin 
    select id_jelo from narudzba where narudzba.id_rezervacija = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ponuda_jela__dodaj_jelo`(p_id_ponuda int, p_id_jelo int)
begin 
    insert into ponuda(id_ponude_jela, id_jelo)
      values (p_id_ponuda,p_id_jelo);
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ponuda_jela__ucitaj_jela`(p_id INT)
BEGIN
    SELECT
      jelo.*
    FROM ponuda join jelo on jelo.id_jelo = ponuda.id_jelo
    WHERE ponuda.id_ponude_jela = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ponuda_jela__ucitaj_naziv`(p_id int)
begin 
    select naziv_ponuda
    from ponuda_jela
    where
      ponuda_jela.id_ponuda = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ponuda_jela__ukloni_jelo`(p_id_ponuda int, p_id_jelo int)
begin
    delete from ponuda 
    where id_ponude_jela = p_id_ponuda and id_jelo = p_id_jelo;
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `raspored_stolova__dodaj_stol`(p_ugostitelj INT, p_rbr INT, p_kapacitet INT)
BEGIN
    INSERT INTO stolovi (
      id_ugostitelj
      , rbr_stol
      , kapacitet
    )
    VALUES (
      p_ugostitelj
      , p_rbr
      , p_kapacitet
    );
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `raspored_stolova__ucitaj_stolove`(p_ugostitelj INT)
BEGIN
    SELECT
      stolovi.*
    FROM stolovi
    WHERE stolovi.id_ugostitelj = p_ugostitelj ORDER BY rbr_stol;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `raspored_stolova__ukloni_stol`(p_id INT)
BEGIN
    DELETE FROM stolovi
    WHERE stolovi.id_stol = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `raspored_stolova__uredi_stol`(p_id INT, p_rbr INT, p_kapacitet INT)
BEGIN
    UPDATE stolovi
    SET
      stolovi.rbr_stol    = p_rbr
      , stolovi.kapacitet = p_kapacitet
    WHERE
      stolovi.id_stol = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `recenzija__objavi`(p_ugostitelj INT, p_korisnik INT, p_ocjena INT, p_tekst VARCHAR(2048))
BEGIN
    IF ((SELECT id_vrsta
        FROM korisnik
        WHERE korisnik.id = p_korisnik) NOT IN (0, 4) and p_korisnik is not null)
       OR (SELECT id_vrsta
           FROM korisnik
           WHERE korisnik.id = p_ugostitelj) <> 1
    THEN
      SIGNAL SQLSTATE 'ERR0R'
      SET MESSAGE_TEXT = 'Nevaljani poziv objave recenzije';
    END IF;

    IF (SELECT id_vrsta
        FROM korisnik
        WHERE korisnik.id = p_korisnik) = 4
    THEN
      delete from recenzija where recenzija.id_ugostitelj = p_ugostitelj and id_autor in(select id from korisnik where id_vrsta = 4);

    END IF;

    INSERT INTO recenzija (id_ugostitelj, id_autor, ocjena, tekst) VALUES (p_ugostitelj, p_korisnik, p_ocjena, p_tekst);

  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__dodaj_jelo`(p_id_rezervacije INT, p_id_jelo INT, p_kolicina INT)
BEGIN
    INSERT INTO narudzba (id_jelo, id_rezervacija, kolicina)
    VALUES (p_id_jelo,p_id_rezervacije, p_kolicina);
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__dodaj_stol`(p_id_rezervacija INT, p_id_stol INT, p_vrijeme_pocetak INT, p_vrijeme_kraj INT)
BEGIN

    IF exists(SELECT zauzeti_stolovi_u_vremenu.id_stol
              FROM zauzeti_stolovi_u_vremenu
              WHERE zauzeti_stolovi_u_vremenu.vrijeme_kraj_epoch BETWEEN p_vrijeme_pocetak AND p_vrijeme_kraj - 59
                    OR zauzeti_stolovi_u_vremenu.vrijeme_pocetak_epoch BETWEEN p_vrijeme_pocetak AND p_vrijeme_kraj - 59 AND
                       zauzeti_stolovi_u_vremenu.id_stol = p_id_stol)
    THEN
      signal SQLSTATE '45000' set MESSAGE_TEXT = 'Stol je rezerviran u tom terminu';
    END IF;

    INSERT INTO rezervacija_stol (id_rezervacija, id_stol) VALUES (p_id_rezervacija, p_id_stol);
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__dohvati_dostupnost_stolova_u_terminu`(
  p_start           INT,
  p_end             INT,
  p_broj_gostiju    INT,
  p_id_ugostitelj   INT,
  p_bez_rezervacije INT
)
BEGIN

    DECLARE nerasporedeni_gosti INT;
    DECLARE tmp_id INT;
    DECLARE tmp_kapacitet INT;

    DROP TEMPORARY TABLE IF EXISTS dostupniTmp, dostupnostTmp, prijedlog;


    CREATE TEMPORARY TABLE dostupnostTmp (
      id_stol    INT,
      kapacitet  INT,
      dostupnost BOOL
    );

    CREATE TEMPORARY TABLE dostupniTmp (
      id_stol   INT,
      kapacitet INT
    );

    CREATE TEMPORARY TABLE prijedlog (
      id_stol INT
    );

    INSERT INTO dostupnostTmp
      SELECT
        stolovi.id_stol,
        stolovi.kapacitet,
        stolovi.id_stol IN (SELECT id_stol
                            FROM rezervacija_stol
                            WHERE id_rezervacija = p_bez_rezervacije) OR (stolovi.id_stol NOT IN (
          SELECT DISTINCT zauzeti_stolovi_u_vremenu.id_stol
          FROM zauzeti_stolovi_u_vremenu
          WHERE zauzeti_stolovi_u_vremenu.vrijeme_kraj_epoch BETWEEN p_start AND p_end - 59
                OR zauzeti_stolovi_u_vremenu.vrijeme_pocetak_epoch BETWEEN p_start AND p_end - 59
        ) AND u_radnom_vremenu(p_start, p_end, p_id_ugostitelj)) AS dostupnost
      FROM stolovi
      WHERE
        stolovi.id_ugostitelj = p_id_ugostitelj;


    INSERT INTO dostupniTmp
      SELECT
        id_stol,
        kapacitet
      FROM dostupnostTmp
      WHERE dostupnost;


    SET nerasporedeni_gosti = p_broj_gostiju;


    WHILE nerasporedeni_gosti > 0 AND exists(SELECT *
                                             FROM dostupniTmp) DO
      SELECT
        NULL,
        NULL
      INTO tmp_id, tmp_kapacitet;

      SELECT
        id_stol,
        kapacitet
      INTO tmp_id, tmp_kapacitet
      FROM dostupniTmp
      WHERE dostupniTmp.kapacitet >= nerasporedeni_gosti
      ORDER BY kapacitet ASC
      LIMIT 1;


      IF tmp_id IS NULL
      THEN
        SELECT
          id_stol,
          kapacitet
        INTO tmp_id, tmp_kapacitet
        FROM dostupniTmp
        ORDER BY kapacitet DESC
        LIMIT 1;


      END IF;

      DELETE FROM dostupniTmp
      WHERE dostupniTmp.id_stol = tmp_id;
      INSERT INTO prijedlog (id_stol) VALUES (tmp_id);
      SET nerasporedeni_gosti = nerasporedeni_gosti - tmp_kapacitet;


    END WHILE;

    SELECT
      *,
      id_stol IN (SELECT id_stol
                  FROM prijedlog) AS prijedlog
    FROM dostupnostTmp;

    DROP TEMPORARY TABLE IF EXISTS dostupniTmp, dostupnostTmp, prijedlog;


  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__dohvati_jela`(p_id INT)
BEGIN
    SELECT *
    FROM narudzba
    WHERE narudzba.id_rezervacija = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__dohvati_stolove`(p_id INT)
BEGIN
    SELECT *
    FROM rezervacija_stol
    WHERE rezervacija_stol.id_rezervacija = p_id;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__kreiraj`(p_gost int, p_ugostitelj int)
begin 
    insert into rezervacija(id_gost,id_ugostitelj) values (p_gost,p_ugostitelj);
    select LAST_INSERT_ID() as id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__obrisi`(p_id_rezervacija int, p_id_gost int)
BEGIN 
    delete from rezervacija where rezervacija.id_gost = p_id_gost and rezervacija.id_rezervacija = p_id_rezervacija;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__osvjezi_osnovno`(
  p_id_rezervacije INT,
  p_pocetak        int,
  p_kraj           int,
  p_broj_osoba     INT
)
BEGIN
    UPDATE rezervacija
    SET
      vrijeme_pocetak = FROM_UNIXTIME(p_pocetak),
      vrijeme_kraj    = FROM_UNIXTIME(p_kraj),
      broj_osoba      = p_broj_osoba
    where id_rezervacija = p_id_rezervacije;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__ucitaj`(p_id_rezervacija int)
begin 
    select * from rezervacija where rezervacija.id_rezervacija = p_id_rezervacija;
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__ukloni_jela`(p_id_rezervacije INT)
BEGIN
    DELETE FROM narudzba
    WHERE id_rezervacija = p_id_rezervacije;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `rezervacija__ukloni_stolove`(p_id int)
BEGIN
  delete from rezervacija_stol where rezervacija_stol.id_rezervacija = p_id;
end$$

CREATE DEFINER=`root`@`%` PROCEDURE `stanje_zaliha__dodaj_namirnica`(p_id_ugosititelj INT, p_naziv VARCHAR(128), p_kolicina INT)
BEGIN
    INSERT INTO namirnica (naziv, id_ugostitelj, kolicina)
    VALUES (p_naziv, p_id_ugosititelj, p_kolicina);
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `stanje_zaliha__ucitaj_namirnice`(p_id_ugostitelj int)
begin 
    select * from namirnica where namirnica.id_ugostitelj = p_id_ugostitelj;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `stanje_zaliha__ukloni_namirnica`(p_id_namirnica INT)
BEGIN
    delete from namirnica where id_namirnica = p_id_namirnica;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `stanje_zaliha__uredi_namirnica`(p_id_namirnica INT, p_kolicina INT)
BEGIN
    update namirnica set kolicina = p_kolicina where id_namirnica = p_id_namirnica;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__dohvati_ocjena`(p_id INT)
BEGIN
    DECLARE max INT;
    DECLARE inspekcija_ocjena INT;
    DECLARE inspekcija_tekst VARCHAR(2048);

    SELECT
      recenzija.ocjena,
      recenzija.tekst
    INTO inspekcija_ocjena, inspekcija_tekst
    FROM recenzija
    WHERE id_ugostitelj = p_id AND (id_autor IN (SELECT id
                                                FROM korisnik
                                                WHERE id_vrsta = 4))
    ORDER BY vremenska_oznaka DESC
    LIMIT 1;


    SELECT max(suma)
    INTO max
    FROM (SELECT count(ocjena) AS suma
          FROM recenzija
          WHERE id_ugostitelj = p_id
          GROUP BY ocjena) AS ocjene;

    SELECT
      (SELECT round(avg(ocjena), 1)
       FROM recenzija
       WHERE id_ugostitelj = p_id AND( id_autor IN (SELECT id
                                                   FROM korisnik
                                                   WHERE id_vrsta = 0) OR id_autor IS NULL))                AS 'ukupna_ocjena',
      (SELECT round(count(ocjena))
       FROM recenzija
       WHERE id_ugostitelj = p_id AND (id_autor IN (SELECT id
                                                   FROM korisnik
                                                   WHERE id_vrsta = 0) OR id_autor IS
                                                                          NULL))                            AS 'broj_ocjena',
      (SELECT round(count(ocjena) * 100 / max)
       FROM recenzija
       WHERE id_ugostitelj = p_id AND ocjena = 1 AND (id_autor IN (SELECT id
                                                                  FROM korisnik
                                                                  WHERE id_vrsta = 0) OR id_autor IS NULL)) AS 'udio_1',
      (SELECT round(count(ocjena) * 100 / max)
       FROM recenzija
       WHERE id_ugostitelj = p_id AND ocjena = 2 AND (id_autor IN (SELECT id
                                                                  FROM korisnik
                                                                  WHERE id_vrsta = 0) OR id_autor IS NULL)) AS 'udio_2',
      (SELECT round(count(ocjena) * 100 / max)
       FROM recenzija
       WHERE id_ugostitelj = p_id AND ocjena = 3 AND (id_autor IN (SELECT id
                                                                  FROM korisnik
                                                                  WHERE id_vrsta = 0) OR id_autor IS NULL)) AS 'udio_3',
      (SELECT round(count(ocjena) * 100 / max)
       FROM recenzija
       WHERE id_ugostitelj = p_id AND ocjena = 4 AND (id_autor IN (SELECT id
                                                                  FROM korisnik
                                                                  WHERE id_vrsta = 0) OR id_autor IS NULL)) AS 'udio_4',
      (SELECT round(count(ocjena) * 100 / max)
       FROM recenzija
       WHERE id_ugostitelj = p_id AND ocjena = 5 AND (id_autor IN (SELECT id
                                                                  FROM korisnik
                                                                  WHERE id_vrsta = 0) OR id_autor IS NULL)) AS 'udio_5',
      if(inspekcija_ocjena IS NULL, -1,
         inspekcija_ocjena)                                                                                AS 'inspekcija_ocjena',
      if(inspekcija_tekst IS NULL, '',
         inspekcija_tekst)                                                                                 AS 'inspekcija_tekst';

  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__dohvati_recenzije`(p_id int)
BEGIN 
    select recenzija.tekst,
      date(recenzija.vremenska_oznaka) as datum,
     if(korisnicko_ime is not null,korisnicko_ime,'Anonimno') as nadimak,
      id_vrsta,
    concat(ocjena,'.0') as ocjena from recenzija left join korisnik on korisnik.id = recenzija.id_autor where id_ugostitelj = p_id;
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__dohvati_rezervacije`(p_id_ugostitelj INT)
BEGIN

    DECLARE unix_min INT;
    DECLARE unix_max INT;

    SET unix_min = unix_timestamp((
                                    SELECT min(vrijeme_pocetak)
                                    FROM rezervacija
                                    WHERE id_ugostitelj = p_id_ugostitelj AND broj_osoba >= 0 and day(vrijeme_pocetak) = day(now())
                                  ));

    SET unix_max = unix_timestamp((
                                    SELECT max(vrijeme_kraj)
                                    FROM rezervacija
                                    WHERE id_ugostitelj = p_id_ugostitelj AND broj_osoba >= 0 and day(vrijeme_pocetak) = day(now())
                                  ));

    SELECT
      rezervacija.id_rezervacija,
      gost.id_gost,
      gost.ime_prezime,
      gost.br_kartice,
      gost.br_telefona,
      gost.email,
      unix_timestamp(rezervacija.vrijeme_pocetak)                                          AS vrijeme_pocetak,
      unix_timestamp(rezervacija.vrijeme_kraj)                                             AS vrijeme_kraj,
      rezervacija.broj_osoba,
      round((unix_timestamp(rezervacija.vrijeme_pocetak) - unix_min) / (unix_max - unix_min)*100)  AS relPocetak,
      round((unix_timestamp(rezervacija.vrijeme_kraj) - unix_min) / (unix_max - unix_min)*100+1) AS relKraj
    FROM rezervacija
      left JOIN gost ON gost.id_gost = rezervacija.id_gost
    WHERE id_ugostitelj = p_id_ugostitelj and rezervacija.vrijeme_kraj>now()
          AND broj_osoba >= 0 ORDER BY vrijeme_pocetak ASC;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__dohvati_statistiku_trenutna`(p_id INT)
BEGIN
    DECLARE v_prihod DECIMAL;
    DECLARE v_tjedni_prihod DECIMAL;
    DECLARE v_rashod DECIMAL;
    DECLARE v_tjedni_rashod DECIMAL;
    

    SELECT round(sum(narudzba.kolicina * jelo.cijena_jela), 2)
    INTO v_prihod
    FROM rezervacija
      JOIN narudzba ON rezervacija.id_rezervacija = narudzba.id_rezervacija
      JOIN jelo ON jelo.id_jelo = narudzba.id_jelo
    WHERE subdate(now(), INTERVAL 7 DAY) < rezervacija.vrijeme_kraj AND rezervacija.id_ugostitelj = p_id;


    SELECT round(
        (
          SELECT avg(suma) * 7 AS pros_prihod
          FROM (
                 SELECT if(sum(n.kolicina * j.cijena_jela) IS NULL, 0, sum(n.kolicina * j.cijena_jela)) AS suma
                 FROM rezervacija AS r
                   LEFT JOIN narudzba AS n ON r.id_rezervacija = n.id_rezervacija
                   LEFT JOIN jelo AS j ON n.id_jelo = j.id_jelo
                 WHERE r.id_ugostitelj = p_id
                 GROUP BY day(r.vrijeme_kraj)) AS tmp
        ), 2
    )
    INTO v_tjedni_prihod;


    SELECT round(sum(nabava_namirnice.kolicina), 2)
    INTO v_rashod
    FROM nabava
      LEFT JOIN nabava_namirnice ON nabava.id_nabava = nabava_namirnice.id_nabava
    WHERE subdate(now(), INTERVAL 7 DAY) < nabava.vremenska_oznaka AND nabava.id_ugostitelj = p_id;


    SELECT round(
        (
          SELECT avg(suma) * 7 AS pros_rashod
          FROM (
                 SELECT if(sum(nn.kolicina) IS NULL, 0, sum(nn.kolicina)) AS suma
                 FROM nabava AS n
                   LEFT JOIN nabava_namirnice AS nn ON n.id_nabava = nn.id_nabava
                 WHERE n.id_ugostitelj = p_id
                 GROUP BY day(n.vremenska_oznaka)) AS tmp
        ), 2
    )
    INTO v_tjedni_rashod;

    
    select v_prihod as prihod,v_tjedni_prihod as tjedni_prihod,v_rashod as rashod,v_tjedni_rashod as tjedni_rashod ,v_prihod-v_rashod as profit,v_tjedni_prihod-v_tjedni_rashod as tjedni_profit;

  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__dohvati_statistiku_u_vremenu`(p_id INT)
BEGIN
    DECLARE prvi_dan DATE;
    DECLARE zadnji_dan DATE;

    DROP TEMPORARY TABLE IF EXISTS dani;
    CREATE TEMPORARY TABLE dani (
      dan DATE
    );

    SELECT min(m)
    INTO prvi_dan
    FROM (SELECT min(vrijeme_pocetak) AS m
          FROM rezervacija
          WHERE id_ugostitelj = 15
          UNION
          SELECT min(vremenska_oznaka) AS m
          FROM nabava
          WHERE id_ugostitelj = 15) AS mm;

    SELECT max(m)
    INTO zadnji_dan
    FROM (SELECT max(vrijeme_kraj) AS m
          FROM rezervacija
          WHERE id_ugostitelj = 15
          UNION
          SELECT max(vremenska_oznaka) AS m
          FROM nabava
          WHERE id_ugostitelj = 15) AS mm;
    
    
    REPEAT 
      insert into dani(dan) VALUES (date(prvi_dan));
      set prvi_dan = adddate(prvi_dan,INTERVAL  1 day);  
      until zadnji_dan<prvi_dan
      
      end REPEAT ;

    select day(dan) as dan,month(dan)-1 as mjesec, year(dan) as godina, if(round(sum(narudzba.kolicina*jelo.cijena_jela),2) is not null,round(sum(narudzba.kolicina*jelo.cijena_jela),2),0) as prihod, if(round(sum(nabava_namirnice.kolicina),2) is not null,round(sum(nabava_namirnice.kolicina),2),0) as rashod
      
    from dani left join rezervacija on dani.dan = date(rezervacija.vrijeme_kraj)
      left JOIN narudzba ON rezervacija.id_rezervacija = narudzba.id_rezervacija
      left JOIN jelo ON jelo.id_jelo = narudzba.id_jelo
      left join nabava on dani.dan = date(nabava.vremenska_oznaka)
      left join nabava_namirnice on nabava.id_nabava = nabava_namirnice.id_nabava
    group by dan;
    DROP TEMPORARY TABLE IF EXISTS dani;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__opskrbi_nabavom`(p_id_nabava INT, p_id_ugostitelj INT)
BEGIN
    DROP TEMPORARY TABLE IF EXISTS apdejt;

    CREATE TEMPORARY TABLE apdejt (
      id_namirnica INT,
      kolicina     INT
    );
    IF (exists(SELECT *
               FROM nabava_namirnice
               WHERE id_nabava = p_id_nabava))
    THEN
      INSERT INTO apdejt SELECT
                           namirnica.id_namirnica,
                           namirnica.kolicina + nabava_namirnice.kolicina AS kolicina
                         FROM nabava_namirnice
                           JOIN namirnica ON namirnica.id_namirnica = nabava_namirnice.id_namirnica
                         WHERE id_nabava = p_id_nabava AND id_ugostitelj = p_id_ugostitelj;

      UPDATE namirnica
        JOIN apdejt ON namirnica.id_namirnica = apdejt.id_namirnica
      SET namirnica.kolicina = apdejt.kolicina;
    END IF;
update nabava SET status=3 where p_id_nabava = id_nabava;
    DROP TEMPORARY TABLE IF EXISTS apdejt;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__osvjezi`(p_id int,p_imeRestoran VARCHAR(128),p_adresa VARCHAR(128),p_opis VARCHAR(2048),p_vrste VARCHAR(2048),p_email VARCHAR(256),p_urlLokacije VARCHAR(2048),p_urlRasporeda VARCHAR(2048),p_roR time,p_rdR time,p_ros time,p_rds time,p_ron time,p_rdn time)
BEGIN
    UPDATE ugostitelj set ime_restoran = p_imeRestoran, adresa = p_adresa,opis = p_opis,vrste_restoran = p_vrste,email = p_email,url_slike_lokala = p_urlLokacije,url_slike_stolova = p_urlRasporeda where id_ugostitelj = p_id;
    delete from radno_vrijeme where id_ugostitelj = p_id;
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,0,p_roR,p_rdR);
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,1,p_roR,p_rdR);
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,2,p_roR,p_rdR);
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,3,p_roR,p_rdR);
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,4,p_roR,p_rdR);
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,5,p_ros,p_rds);
    insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,6,p_ron,p_rdn);
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__registriraj`(p_id int,p_ime varchar(128),p_email varchar(2048),p_adresa varchar(128))
BEGIN 
    insert into ugostitelj(id_ugostitelj, ime_restoran,email, adresa)
      VALUES (p_id,p_ime,p_email,p_adresa);
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,0,'8:00','22:00');
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,1,'8:00','22:00');
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,2,'8:00','22:00');
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,3,'8:00','22:00');
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,4,'8:00','22:00');
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,5,'8:00','22:00');
  insert into radno_vrijeme(id_ugostitelj, id_dan, radi_od, radi_do) VALUES (p_id,6,'8:00','22:00');
    end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__ucitaj`(p_id_ugostitelj INT)
BEGIN
    SELECT
      ugostitelj.*,
      exists(SELECT *
             FROM recenzija
             WHERE ocjena > 1 AND id_autor IN (SELECT id
                                               FROM korisnik
                                               WHERE id_vrsta = 4) and id_ugostitelj = p_id_ugostitelj) AS prihvacen_inspekcija
    FROM ugostitelj
    WHERE ugostitelj.id_ugostitelj = p_id_ugostitelj;
  END$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__ucitaj_radno_vrijeme`(p_id_ugostitelj int)
BEGIN
    select radi_od,radi_do,id_dan from radno_vrijeme where id_ugostitelj = p_id_ugostitelj and id_dan in (0,5,6);
  end$$

CREATE DEFINER=`root`@`%` PROCEDURE `ugostitelj__ucitaj_vrste_restorana`(p_id INT)
BEGIN
    SELECT
      vrsta
    FROM vrsta_restorana
    WHERE vrsta_restorana.id_ugostitelj = p_id;
  END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `radi_do`(`rest` INT, `datum` DATE) RETURNS time
    NO SQL
begin
return (SELECT radi_do From radno_vrijeme where id_restoran = rest and id_dan = dayofweek(datum));
end$$

CREATE DEFINER=`root`@`localhost` FUNCTION `radi_od`(rest int, datum date) RETURNS time
begin
return (SELECT radi_od From radno_vrijeme where id_restoran = rest and id_dan = dayofweek(datum));
end$$

CREATE DEFINER=`root`@`%` FUNCTION `regex_replace`(pattern VARCHAR(2048),replacement VARCHAR(2048),original VARCHAR(2048)) RETURNS varchar(2048) CHARSET latin2 COLLATE latin2_croatian_ci
    DETERMINISTIC
BEGIN
    DECLARE temp VARCHAR(2048);
    DECLARE ch VARCHAR(1);
    DECLARE i INT;
    SET i = 1;
    SET temp = '';
    IF original REGEXP pattern THEN
      loop_label: LOOP
        IF i>CHAR_LENGTH(original) THEN
          LEAVE loop_label;
        END IF;
        SET ch = SUBSTRING(original,i,1);
        IF NOT ch REGEXP pattern THEN
          SET temp = CONCAT(temp,ch);
        ELSE
          SET temp = CONCAT(temp,replacement);
        END IF;
        SET i=i+1;
      END LOOP;
    ELSE
      SET temp = original;
    END IF;
    RETURN temp;
  END$$

CREATE DEFINER=`root`@`%` FUNCTION `ukloni_palatale`(input VARCHAR(2048)) RETURNS varchar(2048) CHARSET latin2 COLLATE latin2_croatian_ci
BEGIN

    DECLARE ret VARCHAR(2048);
    SET ret = replace(replace(replace(
                                  replace(replace(replace(replace(replace(
                                                                      replace(replace(input, '&#263;', 'c'), '&#353;',
                                                                              's'), '&#268;', 'C'),
                                                                  '&#269;', 'c'), '&#382;', 'z'), '&#383;', 'Z'),
                                          '&#272;', 'D'), '&#273;', 'd'), '&#352;', 'S'), '&#262;', 'C');
    SET ret = replace(
        replace(replace(replace(replace(replace(
                                            replace(replace(replace(replace(ret, 'Ć', 'C'), 'ć', 'c'), 'Č', 'C'), 'č',
                                                    'c'), 'đ', 'd'), 'Đ',
                                        'D'), 'Š', 'S'), 'š', 's'), 'Ž', 'Z'), 'ž', 'z');
    RETURN ret;
  END$$

CREATE DEFINER=`root`@`%` FUNCTION `u_radnom_vremenu`(p_ts_od INT, p_ts_do INT, p_id_ugostitelj INT) RETURNS tinyint(1)
BEGIN
    RETURN
    day(from_unixtime(p_ts_od)) = day(from_unixtime(p_ts_do))
    AND (
      SELECT (time(from_unixtime(p_ts_od)) BETWEEN radi_od AND radi_do)
             AND (time(from_unixtime(p_ts_od)) BETWEEN radi_od AND radi_do)
      FROM radno_vrijeme
      WHERE id_ugostitelj = p_id_ugostitelj AND
            id_dan = weekday(from_unixtime(p_ts_od))
    );
  END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `gost`
--

CREATE TABLE IF NOT EXISTS `gost` (
  `id_gost` int(32) NOT NULL,
  `ime_prezime` varchar(64) CHARACTER SET latin2 COLLATE latin2_croatian_ci NOT NULL,
  `br_kartice` varchar(32) DEFAULT NULL,
  `email` varchar(256) NOT NULL,
  `br_telefona` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `gost`
--

INSERT INTO `gost` (`id_gost`, `ime_prezime`, `br_kartice`, `email`, `br_telefona`) VALUES
(71, 'mate', 'je', 'nirujnavi@gmail.com', ''),
(74, 'dg', 'f', 'g', 'f'),
(77, 'dsa', '', 'dsaf', ''),
(80, 'blabla', '', 'Hmm', ''),
(127, 'lovro1@net.hr', '23400093213934456', 'Lovro', '0998212465'),
(129, 'Nika Juki&#263;', '5556748291047', 'nikajukic@gmail.com', '1234567'),
(132, 'ivo.josipovic@tralala', NULL, 'Ivo Josipovi&#263;', NULL),
(133, 'ante', NULL, 'ante', NULL),
(134, 'a', NULL, 'a', NULL),
(135, 'e', NULL, 'e', NULL),
(136, 'c', NULL, 'c', NULL),
(137, 'w', NULL, 'w', NULL),
(138, 'r', 'd', 'b', 'c'),
(139, '4', NULL, '3', NULL),
(147, 'Ja', 'afafsf', 'imegmail.com', 'asfdff'),
(149, 'dg@f', NULL, 'Davor Grbi', NULL),
(151, 'tata tatic', NULL, 'tat@g', NULL),
(152, 'tat tat', NULL, 'tat@t', NULL),
(153, 'logo logic', NULL, 'lg@g', NULL),
(163, 'gostime', '', 'gostmailaaadsdsds', ''),
(164, 'Ime Prezime', '', 'ime.prezime@gmail.com', ''),
(167, '', NULL, 'ime.prezime@gmail.com', NULL),
(168, 'Novi Korisnik', '', 'restoran@gmail.com', ''),
(169, 'Igor Kramaric', NULL, 'wuhu@wuhu.com', NULL),
(170, 'Dorotea Dorotea', NULL, 'dorotea@emaill.eu', NULL),
(173, 'Jamie Oliver', NULL, 'jamie@mailinator.com', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jelo`
--

CREATE TABLE IF NOT EXISTS `jelo` (
`id_jelo` int(11) NOT NULL,
  `id_ugostitelj` int(11) DEFAULT NULL,
  `ime_jela` varchar(128) CHARACTER SET latin2 COLLATE latin2_croatian_ci NOT NULL,
  `cijena_jela` decimal(6,2) NOT NULL,
  `id_vrsta_jela` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jelo`
--

INSERT INTO `jelo` (`id_jelo`, `id_ugostitelj`, `ime_jela`, `cijena_jela`, `id_vrsta_jela`) VALUES
(56, 121, 'zelena', 13.00, 4),
(57, 121, 'krumpiri', 20.00, 3),
(58, 121, 'cola', 10.00, 6),
(59, 141, 'ENCHILADAS"JALISCO" 2 TORTILJE ZAPE&#268;ENE SIROM', 30.00, 1),
(61, 141, 'VARIVO OD PORILUKA + PRILOG', 27.00, 2),
(62, 141, 'MIJE&#352;ANI WOK + PRILOG', 32.00, 2),
(71, 15, 'Biftek u umaku od gljiva', 45.00, 2),
(72, 15, '&#262;evapi', 35.00, 2),
(73, 15, 'Gove&#273;a juha', 15.00, 1),
(74, 15, 'Lepinja', 8.00, 3),
(75, 15, 'Sezonska salata', 15.00, 4),
(76, 15, '&#352;trukli sa sirom', 20.00, 5),
(77, 15, 'Mlinci', 16.00, 3),
(78, 15, 'Pivo', 17.00, 6),
(79, 142, 'Juha od krumpira', 15.00, 1),
(80, 142, 'Pr&#382;eni krumpir', 20.00, 3),
(81, 142, 'Restani krumpir', 20.00, 3),
(82, 142, 'Pekarski krumpir', 20.00, 3),
(83, 142, 'Krumpir salata', 18.00, 4),
(85, 142, 'Pire krumpir', 20.00, 3),
(86, 154, 'Zape&#269;ena tortilja sa sirom', 20.00, 1),
(87, 154, 'Pr&#382;ena tortilja s piletinom', 35.00, 1),
(88, 154, 'Enchiladas', 35.00, 2),
(89, 154, 'Tacos', 25.00, 2),
(90, 154, 'Burritos', 30.00, 2),
(91, 154, 'Meksi&#269;ka ri&#382;a', 10.00, 3),
(92, 154, 'Chili grah', 8.00, 3),
(93, 154, 'Kukuruz', 5.00, 3),
(94, 154, 'Salata Mexicana', 25.00, 4),
(95, 154, 'Salata Sombrero', 30.00, 4),
(96, 154, 'Sezonska salata', 15.00, 4),
(97, 154, 'Pr&#382;ena tortilja punjena vo&#263;em', 20.00, 5),
(98, 154, 'Pohana tortilja sa slatkim sirom', 18.00, 5),
(99, 154, 'Pita s limetom', 15.00, 5),
(100, 154, 'Krem juha od kukuruza', 20.00, 1),
(101, 154, 'Meksi&#269;ka kava', 27.00, 6),
(102, 154, 'Tequila sunrise', 40.00, 6),
(103, 154, 'Cola', 15.00, 6);

-- --------------------------------------------------------

--
-- Table structure for table `konobar`
--

CREATE TABLE IF NOT EXISTS `konobar` (
  `id_konobar` int(11) NOT NULL,
  `id_ugostitelj` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `konobar`
--

INSERT INTO `konobar` (`id_konobar`, `id_ugostitelj`) VALUES
(75, 15),
(107, 15),
(128, 119),
(155, 154),
(156, 154),
(158, 157),
(159, 157);

-- --------------------------------------------------------

--
-- Table structure for table `korisnik`
--

CREATE TABLE IF NOT EXISTS `korisnik` (
`id` int(11) NOT NULL,
  `korisnicko_ime` varchar(32) CHARACTER SET latin2 COLLATE latin2_croatian_ci NOT NULL,
  `lozinka` varchar(32) CHARACTER SET latin2 COLLATE latin2_croatian_ci NOT NULL,
  `id_vrsta` int(11) NOT NULL,
  `vrijeme_dodavanja` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `korisnik`
--

INSERT INTO `korisnik` (`id`, `korisnicko_ime`, `lozinka`, `id_vrsta`, `vrijeme_dodavanja`) VALUES
(0, 'dobavljac', 'dobavljac', 5, '2015-01-12 02:34:14'),
(15, '15', '15', 1, '2015-01-12 02:34:14'),
(71, 'mate', 'miso', 0, '2015-01-12 02:34:14'),
(72, 'vuco', 'vuco', 1, '2015-01-12 02:34:14'),
(73, 'andreja', 'andreja', 1, '2015-01-12 02:34:14'),
(74, 'd', 'david', 0, '2015-01-12 02:34:14'),
(75, 'konobar', 'konobar', 2, '2015-01-12 02:34:14'),
(77, 'Igor', 'aaa', 0, '2015-01-12 02:34:14'),
(78, 'blabla', 'hehe', 1, '2015-01-12 02:34:14'),
(79, 'nika', '', 1, '2015-01-12 02:34:14'),
(80, 'Buu', 'bla', 0, '2015-01-12 02:34:14'),
(102, 'inspekcija', 'inspekcija', 4, '2015-01-12 02:34:14'),
(107, 'Mirko', 'mirko', 2, '2015-01-12 02:34:14'),
(108, 'admin', 'admin', 3, '2015-01-12 02:34:14'),
(115, 'r1-ivan', 'r1-ivan', 1, '2015-01-12 02:34:14'),
(118, 'David', 'David', 1, '2015-01-12 02:34:14'),
(119, 'Davidenko', 'Davidenko', 1, '2015-01-12 02:34:14'),
(120, 'Davidovi', 'Davidovi', 1, '2015-01-12 02:34:14'),
(121, 'nikajukic', 'krumpirko', 1, '2015-01-12 02:34:14'),
(124, 'r1-lovro', 'r1-lovro', 1, '2015-01-12 02:34:14'),
(125, 'k1-lovro', 'k1-lovro', 2, '2015-01-12 02:34:14'),
(126, 'k2-lovro', 'k2-lovro', 2, '2015-01-12 02:34:14'),
(127, 'Lovro1', 'Lovro1', 0, '2015-01-12 02:34:14'),
(128, 'Aleksandar', 'Aleksandar', 2, '2015-01-12 02:34:14'),
(129, 'nikita', 'nixxon', 0, '2015-01-12 02:34:14'),
(132, 'ivo', 'josipovic', 0, '2015-01-12 02:34:14'),
(133, 'ante', 'ante', 0, '2015-01-12 02:34:14'),
(134, 'a', 'a', 0, '2015-01-12 02:34:14'),
(135, 'e', 'e', 0, '2015-01-12 02:34:14'),
(136, 'c', 'c', 0, '2015-01-12 02:34:14'),
(137, 'w', 'w', 0, '2015-01-12 02:34:14'),
(138, 'r', 'r', 0, '2015-01-12 02:34:14'),
(139, '1', '2', 0, '2015-01-12 03:06:55'),
(140, '2', '3', 1, '2015-01-12 03:07:12'),
(141, 'zmaj', 'zmaj', 1, '2015-01-13 11:49:39'),
(142, 'krumpir', 'volimkrumpir', 1, '2015-01-13 20:42:00'),
(143, '&#353;armer', 'hehehe', 1, '2015-01-13 20:49:37'),
(144, 'ja', 'ja', 1, '2015-01-13 22:09:29'),
(145, 'Tanja', 'tanja', 2, '2015-01-13 22:18:11'),
(146, 'Sanja', 'sanja', 2, '2015-01-13 22:18:19'),
(147, '*korisni&#269;ko...ime#', '1', 0, '2015-01-14 00:04:27'),
(148, 'Restoran1', 'restoran', 1, '2015-01-14 00:26:20'),
(149, 'Davor', 'dg', 0, '2015-01-14 07:57:36'),
(151, 'tata', 'tata', 0, '2015-01-14 08:01:05'),
(152, 'tato', 'tato', 0, '2015-01-14 08:02:46'),
(153, 'logo', 'logo', 0, '2015-01-14 08:19:14'),
(154, 'sombrero', 'nixxon', 1, '2015-01-14 12:10:43'),
(155, 'konobarsom', 'konobar1', 2, '2015-01-14 12:24:36'),
(156, 'konobarsom2', 'konobar2', 2, '2015-01-14 12:25:00'),
(157, '&#353;armer1', 'hehehe', 1, '2015-01-14 12:52:14'),
(158, 'Ivica', 'ivica', 2, '2015-01-14 12:56:52'),
(159, 'Marica', 'marica', 2, '2015-01-14 12:57:06'),
(160, 'korisnicko', 'lozinka', 1, '2015-01-14 14:15:34'),
(161, 'Korime', 'loz', 1, '2015-01-14 14:18:04'),
(162, 'korProba', 'lozinka', 1, '2015-01-14 14:33:09'),
(163, 'Gostkorime', 'gostloz', 0, '2015-01-14 14:33:21'),
(164, '/*Korisni&#269;ko#', 'korisnicko', 0, '2015-01-14 15:38:51'),
(165, 'slkjfds', '123456', 1, '2015-01-14 15:42:14'),
(166, 'daujdbfusjdni', 'niodsjnoifn', 1, '2015-01-14 15:44:13'),
(167, '/*Korisni&#269;ko#1', '12345', 0, '2015-01-14 16:12:57'),
(168, '', '12345', 0, '2015-01-14 16:14:48'),
(169, 'wertzui', 'blabla', 0, '2015-01-15 19:30:03'),
(170, 'Dorotea', 'Dorotea', 0, '2015-01-15 19:30:56'),
(171, 'Nokturno', 'nixxon', 1, '2015-01-15 19:46:02'),
(172, '''; DROP TABLE users;', 'bobby', 1, '2015-01-15 19:57:37'),
(173, 'Jamie_Oliver', 'lozinka', 0, '2015-01-15 20:02:03');

-- --------------------------------------------------------

--
-- Table structure for table `korisnik_vrsta`
--

CREATE TABLE IF NOT EXISTS `korisnik_vrsta` (
  `id_vrsta` int(11) NOT NULL,
  `ime_vrsta` varchar(64) COLLATE latin2_croatian_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_croatian_ci;

--
-- Dumping data for table `korisnik_vrsta`
--

INSERT INTO `korisnik_vrsta` (`id_vrsta`, `ime_vrsta`) VALUES
(0, 'gost'),
(1, 'ugostitelj'),
(2, 'konobar'),
(3, 'admin'),
(4, 'inspekcija'),
(5, 'dobavljac');

-- --------------------------------------------------------

--
-- Table structure for table `nabava`
--

CREATE TABLE IF NOT EXISTS `nabava` (
`id_nabava` int(11) NOT NULL,
  `id_ugostitelj` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `vremenska_oznaka` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_dobavljac` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nabava`
--

INSERT INTO `nabava` (`id_nabava`, `id_ugostitelj`, `status`, `vremenska_oznaka`, `id_dobavljac`) VALUES
(31, 15, 3, '2015-01-08 13:48:49', 0),
(34, 15, 3, '2015-01-08 14:06:02', 0),
(35, 72, 1, '2015-01-08 14:25:12', 0),
(36, 72, 2, '2015-01-08 14:25:18', 0),
(37, 15, 2, '2015-01-08 14:34:58', 0),
(38, 15, 3, '2015-01-08 14:36:12', 0),
(39, 15, 3, '2015-01-09 15:37:28', 0),
(40, 15, 1, '2015-01-11 12:27:55', 0),
(41, 119, 2, '2015-01-11 12:36:51', 0),
(42, 15, 2, '2015-01-12 14:06:05', 0),
(43, 15, 2, '2015-01-12 23:05:02', 0),
(44, 15, 2, '2015-01-13 13:31:24', 0),
(45, 15, 2, '2015-01-14 08:35:25', 0),
(46, 15, 2, '2015-01-14 10:40:29', 0),
(47, 15, 0, '2015-01-14 10:40:49', 0),
(48, 154, 2, '2015-01-15 18:18:25', 0),
(49, 154, 0, '2015-01-15 18:18:34', 0),
(50, 154, 1, '2015-01-15 18:19:02', 0);

-- --------------------------------------------------------

--
-- Table structure for table `nabava_namirnice`
--

CREATE TABLE IF NOT EXISTS `nabava_namirnice` (
  `id_nabava` int(11) NOT NULL,
  `id_namirnica` int(11) NOT NULL,
  `kolicina` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `nabava_namirnice`
--

INSERT INTO `nabava_namirnice` (`id_nabava`, `id_namirnica`, `kolicina`) VALUES
(31, 7, 15),
(35, 18, 10),
(35, 19, 10),
(35, 20, 10),
(36, 18, 50),
(37, 7, 100),
(37, 17, 100),
(38, 7, 5),
(38, 17, 5),
(39, 7, 100),
(39, 17, 100),
(39, 23, 100),
(40, 17, 1),
(40, 23, 1),
(40, 24, 1),
(41, 25, 50),
(41, 27, 50),
(42, 7, 5),
(42, 23, 4),
(43, 7, 2),
(44, 24, 1),
(44, 30, 5),
(45, 7, 50),
(45, 17, 50),
(46, 23, 9),
(47, 24, 19),
(48, 41, 15),
(48, 43, 9),
(49, 40, 6),
(49, 45, 10),
(50, 42, 70),
(50, 43, 50);

-- --------------------------------------------------------

--
-- Table structure for table `namirnica`
--

CREATE TABLE IF NOT EXISTS `namirnica` (
`id_namirnica` int(11) NOT NULL,
  `naziv` varchar(128) CHARACTER SET latin2 COLLATE latin2_croatian_ci NOT NULL,
  `id_ugostitelj` int(11) NOT NULL,
  `kolicina` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `namirnica`
--

INSERT INTO `namirnica` (`id_namirnica`, `naziv`, `id_ugostitelj`, `kolicina`) VALUES
(7, 'Meso', 15, 215),
(17, 'Kruh', 15, 201),
(18, 'Bra&#353;no', 72, 100),
(19, 'Sol', 72, 100),
(20, 'Jaja', 72, 200),
(23, 'Salata', 15, 195),
(24, 'Nutella', 15, 98),
(25, 'Kruh', 119, 50),
(27, 'Jaja', 119, 50),
(30, 'Ne&#353;to', 15, 32),
(40, 'Salata', 154, 150),
(41, 'Grah', 154, 100),
(42, 'Tortilje', 154, 300),
(43, 'Sir', 154, 50),
(44, 'Kukuruz', 154, 100),
(45, 'Piletina', 154, 500);

-- --------------------------------------------------------

--
-- Table structure for table `narudzba`
--

CREATE TABLE IF NOT EXISTS `narudzba` (
  `id_jelo` int(11) NOT NULL,
  `id_rezervacija` int(11) NOT NULL,
  `kolicina` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `narudzba`
--

INSERT INTO `narudzba` (`id_jelo`, `id_rezervacija`, `kolicina`) VALUES
(71, 1140, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ponuda`
--

CREATE TABLE IF NOT EXISTS `ponuda` (
  `id_ponude_jela` int(11) NOT NULL,
  `id_jelo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_croatian_ci;

--
-- Dumping data for table `ponuda`
--

INSERT INTO `ponuda` (`id_ponude_jela`, `id_jelo`) VALUES
(4, 57),
(9, 87),
(8, 89),
(9, 89),
(8, 90),
(9, 91),
(8, 92),
(9, 94),
(8, 95),
(9, 97),
(8, 98),
(9, 101);

-- --------------------------------------------------------

--
-- Table structure for table `ponuda_jela`
--

CREATE TABLE IF NOT EXISTS `ponuda_jela` (
`id_ponuda` int(11) NOT NULL,
  `naziv_ponuda` varchar(64) COLLATE latin2_croatian_ci NOT NULL,
  `id_ugostitelj` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin2 COLLATE=latin2_croatian_ci;

--
-- Dumping data for table `ponuda_jela`
--

INSERT INTO `ponuda_jela` (`id_ponuda`, `naziv_ponuda`, `id_ugostitelj`) VALUES
(4, 'Akcija', 121),
(7, 'akcija', 15),
(8, 'Akcija', 154),
(9, 'Ponuda dana', 154);

-- --------------------------------------------------------

--
-- Table structure for table `radno_vrijeme`
--

CREATE TABLE IF NOT EXISTS `radno_vrijeme` (
  `id_ugostitelj` int(11) NOT NULL,
  `id_dan` int(11) NOT NULL,
  `radi_od` time NOT NULL,
  `radi_do` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_croatian_ci;

--
-- Dumping data for table `radno_vrijeme`
--

INSERT INTO `radno_vrijeme` (`id_ugostitelj`, `id_dan`, `radi_od`, `radi_do`) VALUES
(15, 0, '08:00:00', '23:00:00'),
(15, 1, '08:00:00', '23:00:00'),
(15, 2, '08:00:00', '23:00:00'),
(15, 3, '08:00:00', '23:00:00'),
(15, 4, '08:00:00', '23:00:00'),
(15, 5, '08:00:00', '23:00:00'),
(15, 6, '08:00:00', '23:00:00'),
(119, 0, '08:00:00', '22:00:00'),
(119, 1, '08:00:00', '22:00:00'),
(119, 2, '08:00:00', '22:00:00'),
(119, 3, '08:00:00', '22:00:00'),
(119, 4, '08:00:00', '22:00:00'),
(119, 5, '08:00:00', '22:00:00'),
(119, 6, '08:00:00', '21:00:00'),
(120, 0, '08:00:00', '22:00:00'),
(120, 1, '08:00:00', '22:00:00'),
(120, 2, '08:00:00', '22:00:00'),
(120, 3, '08:00:00', '22:00:00'),
(120, 4, '08:00:00', '22:00:00'),
(120, 5, '08:00:00', '22:00:00'),
(120, 6, '08:00:00', '22:00:00'),
(121, 0, '08:00:00', '21:00:00'),
(121, 1, '08:00:00', '21:00:00'),
(121, 2, '08:00:00', '21:00:00'),
(121, 3, '08:00:00', '21:00:00'),
(121, 4, '08:00:00', '21:00:00'),
(121, 5, '09:00:00', '22:00:00'),
(121, 6, '10:00:00', '21:00:00'),
(141, 0, '08:00:00', '22:00:00'),
(141, 1, '08:00:00', '22:00:00'),
(141, 2, '08:00:00', '22:00:00'),
(141, 3, '08:00:00', '22:00:00'),
(141, 4, '08:00:00', '22:00:00'),
(141, 5, '08:00:00', '22:00:00'),
(141, 6, '08:00:00', '22:00:00'),
(142, 0, '10:00:00', '22:00:00'),
(142, 1, '10:00:00', '22:00:00'),
(142, 2, '10:00:00', '22:00:00'),
(142, 3, '10:00:00', '22:00:00'),
(142, 4, '10:00:00', '22:00:00'),
(142, 5, '10:00:00', '22:00:00'),
(142, 6, '10:00:00', '22:00:00'),
(154, 0, '08:00:00', '22:00:00'),
(154, 1, '08:00:00', '22:00:00'),
(154, 2, '08:00:00', '22:00:00'),
(154, 3, '08:00:00', '22:00:00'),
(154, 4, '08:00:00', '22:00:00'),
(154, 5, '09:00:00', '23:00:00'),
(154, 6, '10:00:00', '21:00:00'),
(157, 0, '09:00:00', '22:00:00'),
(157, 1, '09:00:00', '22:00:00'),
(157, 2, '09:00:00', '22:00:00'),
(157, 3, '09:00:00', '22:00:00'),
(157, 4, '09:00:00', '22:00:00'),
(157, 5, '09:00:00', '22:00:00'),
(157, 6, '10:00:00', '20:00:00'),
(171, 0, '08:00:00', '22:00:00'),
(171, 1, '08:00:00', '22:00:00'),
(171, 2, '08:00:00', '22:00:00'),
(171, 3, '08:00:00', '22:00:00'),
(171, 4, '08:00:00', '22:00:00'),
(171, 5, '08:00:00', '22:00:00'),
(171, 6, '08:00:00', '22:00:00'),
(172, 0, '08:00:00', '22:00:00'),
(172, 1, '08:00:00', '22:00:00'),
(172, 2, '08:00:00', '22:00:00'),
(172, 3, '08:00:00', '22:00:00'),
(172, 4, '08:00:00', '22:00:00'),
(172, 5, '08:00:00', '22:00:00'),
(172, 6, '08:00:00', '22:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `recenzija`
--

CREATE TABLE IF NOT EXISTS `recenzija` (
`id_recenzija` int(11) NOT NULL,
  `id_ugostitelj` int(11) NOT NULL,
  `id_autor` int(11) DEFAULT NULL,
  `ocjena` int(11) NOT NULL,
  `tekst` varchar(2048) CHARACTER SET utf32 COLLATE utf32_croatian_ci NOT NULL,
  `vremenska_oznaka` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `recenzija`
--

INSERT INTO `recenzija` (`id_recenzija`, `id_ugostitelj`, `id_autor`, `ocjena`, `tekst`, `vremenska_oznaka`) VALUES
(30, 72, 71, 5, 'E NAJBOLJI JE', '2015-01-11 19:51:53'),
(33, 119, 71, 4, 'Super', '2015-01-12 17:42:02'),
(41, 142, 102, 5, 'Nakon &#353;to posjetite Skromni krumpir, ako dosad nije, Va&#353;a omiljena namirnica &#263;e postati krumpir. Krumpir se mo&#382;e ku&#353;ati na mnogo razli&#269;itih na&#269;ina i nijedan ne odudara po kvaliteti od ostalih.', '2015-01-13 23:40:23'),
(44, 154, 71, 5, 'Odli&#269;ne tortilje i salata Sombrero!', '2015-01-14 12:41:18'),
(45, 142, 129, 3, 'Krumpiri su bili malo sirovi.', '2015-01-14 12:43:36'),
(46, 15, 102, 5, 'Savr&#353;eno!', '2015-01-14 15:07:06'),
(50, 119, NULL, 4, 'nelo&#353;e', '2015-01-15 14:46:47'),
(53, 154, 129, 3, 'Tortilje nisu bile dovoljno zape&#269;ene.', '2015-01-15 19:27:55'),
(54, 154, NULL, 4, 'Jako mi se svi&#273;a ovaj restoran!', '2015-01-15 19:28:26'),
(55, 154, 127, 4, 'Odli&#269;an restoran', '2015-01-15 19:28:49'),
(56, 154, NULL, 5, 'Moj najdra&#382;i meksi&#269;ki restoran, najbolje tortilje u gradu!!', '2015-01-15 19:29:16'),
(57, 154, 102, 4, 'Vrlo ugodan ambijent i jako fina hrana. Porcije su obilne, a cijene vrlo prihvatljive. Preporu&#269;amo pr&#382;enu tortilju s piletinom i specijalitet meksi&#269;ku kavu.', '2015-01-15 19:31:21'),
(58, 154, 169, 1, 'U&#382;as! ', '2015-01-15 19:31:43'),
(59, 154, 170, 4, 'Odli&#269;na meksi&#269;ka kava! Osoblje malo nepristojno.', '2015-01-15 19:31:47'),
(60, 141, 102, 1, 'Hrana bez okusa. Osoblje neljubazno.', '2015-01-15 19:38:58'),
(61, 72, 102, 4, 'Odli&#269;an restoran i vrlo ugodan ambijent.', '2015-01-15 19:39:28'),
(62, 119, 102, 2, 'Pizze nisu ni&#353;ta posebno te im nedostaje zanimljiva ponuda egzoti&#269;nih pizza. Prostor je tako&#273;er bio dosta neuredan, a konobari nepristojni. ', '2015-01-15 19:40:52'),
(65, 72, 129, 4, 'Odli&#269;ne juhe!', '2015-01-15 19:53:34'),
(66, 15, 129, 5, 'Najfiniji odrezak u gradu!!', '2015-01-15 19:54:49'),
(67, 72, NULL, 1, 'Juha mi do&#353;la hladna. MINUS.', '2015-01-15 19:56:04'),
(68, 15, 173, 4, 'Mecooo. :D', '2015-01-15 20:03:20'),
(69, 119, 173, 3, 'Ne volim Ruse.', '2015-01-15 20:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `rezervacija`
--

CREATE TABLE IF NOT EXISTS `rezervacija` (
`id_rezervacija` int(11) NOT NULL,
  `id_ugostitelj` int(11) NOT NULL,
  `id_gost` int(11) DEFAULT NULL,
  `broj_osoba` int(11) DEFAULT '-1',
  `vrijeme_pocetak` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vrijeme_kraj` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vremenska_oznaka` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB AUTO_INCREMENT=1284 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rezervacija`
--

INSERT INTO `rezervacija` (`id_rezervacija`, `id_ugostitelj`, `id_gost`, `broj_osoba`, `vrijeme_pocetak`, `vrijeme_kraj`, `vremenska_oznaka`) VALUES
(1084, 15, 71, 1, '2015-01-07 10:00:00', '2015-01-07 10:30:00', '2015-01-06 21:53:09'),
(1099, 15, 71, 1, '2015-01-07 08:00:00', '2015-01-07 18:45:00', '2015-01-06 22:27:12'),
(1107, 15, 71, 1, '2015-01-08 14:15:00', '2015-01-08 15:15:00', '2015-01-08 14:02:37'),
(1108, 15, 71, 1, '2015-01-08 14:15:00', '2015-01-08 15:15:00', '2015-01-08 14:03:46'),
(1111, 15, 71, 1, '2015-01-08 18:00:00', '2015-01-08 19:00:00', '2015-01-08 17:48:06'),
(1125, 15, 138, 2, '2015-01-12 16:30:00', '2015-01-12 17:30:00', '2015-01-12 00:08:32'),
(1126, 15, 71, 3, '2015-01-12 22:00:00', '2015-01-12 22:30:00', '2015-01-12 13:53:52'),
(1127, 15, 71, 3, '2015-01-12 14:30:00', '2015-01-12 15:30:00', '2015-01-12 14:18:52'),
(1140, 15, 71, 2, '2015-01-15 11:15:00', '2015-01-15 12:15:00', '2015-01-13 23:09:08'),
(1147, 15, 147, -1, '2015-01-14 00:10:28', '2015-01-14 00:10:28', '2015-01-14 00:10:28'),
(1162, 15, 129, 1, '2015-01-14 21:45:00', '2015-01-14 22:45:00', '2015-01-14 17:01:37'),
(1169, 15, 77, -1, '2015-01-15 09:14:34', '2015-01-15 09:14:34', '2015-01-15 09:14:34'),
(1197, 15, NULL, -1, '2015-01-15 11:25:11', '2015-01-15 11:25:11', '2015-01-15 11:25:11'),
(1198, 15, NULL, -1, '2015-01-15 11:25:12', '2015-01-15 11:25:12', '2015-01-15 11:25:12'),
(1199, 15, NULL, -1, '2015-01-15 11:25:13', '2015-01-15 11:25:13', '2015-01-15 11:25:13'),
(1200, 15, NULL, -1, '2015-01-15 11:25:13', '2015-01-15 11:25:13', '2015-01-15 11:25:13'),
(1201, 15, NULL, -1, '2015-01-15 11:25:13', '2015-01-15 11:25:13', '2015-01-15 11:25:13'),
(1202, 15, NULL, -1, '2015-01-15 11:25:14', '2015-01-15 11:25:14', '2015-01-15 11:25:14'),
(1203, 15, NULL, -1, '2015-01-15 11:25:15', '2015-01-15 11:25:15', '2015-01-15 11:25:15'),
(1204, 15, NULL, -1, '2015-01-15 11:25:16', '2015-01-15 11:25:16', '2015-01-15 11:25:16'),
(1205, 15, NULL, -1, '2015-01-15 11:25:16', '2015-01-15 11:25:16', '2015-01-15 11:25:16'),
(1206, 15, NULL, -1, '2015-01-15 11:25:16', '2015-01-15 11:25:16', '2015-01-15 11:25:16'),
(1207, 15, NULL, -1, '2015-01-15 11:25:17', '2015-01-15 11:25:17', '2015-01-15 11:25:17'),
(1208, 15, NULL, -1, '2015-01-15 11:25:17', '2015-01-15 11:25:17', '2015-01-15 11:25:17'),
(1209, 15, NULL, -1, '2015-01-15 11:25:17', '2015-01-15 11:25:17', '2015-01-15 11:25:17'),
(1210, 15, NULL, -1, '2015-01-15 11:25:17', '2015-01-15 11:25:17', '2015-01-15 11:25:17'),
(1211, 15, NULL, -1, '2015-01-15 11:25:17', '2015-01-15 11:25:17', '2015-01-15 11:25:17'),
(1212, 15, NULL, -1, '2015-01-15 11:25:18', '2015-01-15 11:25:18', '2015-01-15 11:25:18'),
(1213, 15, NULL, -1, '2015-01-15 11:25:18', '2015-01-15 11:25:18', '2015-01-15 11:25:18'),
(1214, 15, NULL, -1, '2015-01-15 11:25:18', '2015-01-15 11:25:18', '2015-01-15 11:25:18'),
(1215, 15, NULL, -1, '2015-01-15 11:25:18', '2015-01-15 11:25:18', '2015-01-15 11:25:18'),
(1216, 15, NULL, -1, '2015-01-15 11:25:18', '2015-01-15 11:25:18', '2015-01-15 11:25:18'),
(1217, 15, NULL, -1, '2015-01-15 11:25:26', '2015-01-15 11:25:26', '2015-01-15 11:25:26'),
(1218, 15, NULL, -1, '2015-01-15 11:25:26', '2015-01-15 11:25:26', '2015-01-15 11:25:26'),
(1219, 15, NULL, -1, '2015-01-15 11:25:26', '2015-01-15 11:25:26', '2015-01-15 11:25:26'),
(1220, 15, NULL, -1, '2015-01-15 11:25:26', '2015-01-15 11:25:26', '2015-01-15 11:25:26'),
(1221, 15, NULL, -1, '2015-01-15 11:25:27', '2015-01-15 11:25:27', '2015-01-15 11:25:27'),
(1222, 15, NULL, -1, '2015-01-15 11:25:27', '2015-01-15 11:25:27', '2015-01-15 11:25:27'),
(1223, 15, NULL, -1, '2015-01-15 11:25:27', '2015-01-15 11:25:27', '2015-01-15 11:25:27'),
(1224, 15, NULL, -1, '2015-01-15 11:25:27', '2015-01-15 11:25:27', '2015-01-15 11:25:27'),
(1225, 15, NULL, -1, '2015-01-15 11:25:27', '2015-01-15 11:25:27', '2015-01-15 11:25:27'),
(1226, 15, NULL, -1, '2015-01-15 11:25:28', '2015-01-15 11:25:28', '2015-01-15 11:25:28'),
(1227, 15, NULL, -1, '2015-01-15 11:25:28', '2015-01-15 11:25:28', '2015-01-15 11:25:28'),
(1228, 15, NULL, -1, '2015-01-15 11:25:28', '2015-01-15 11:25:28', '2015-01-15 11:25:28'),
(1229, 15, NULL, -1, '2015-01-15 11:25:28', '2015-01-15 11:25:28', '2015-01-15 11:25:28'),
(1230, 15, NULL, -1, '2015-01-15 11:25:29', '2015-01-15 11:25:29', '2015-01-15 11:25:29'),
(1231, 15, NULL, -1, '2015-01-15 11:25:40', '2015-01-15 11:25:40', '2015-01-15 11:25:40'),
(1232, 15, NULL, -1, '2015-01-15 11:25:42', '2015-01-15 11:25:42', '2015-01-15 11:25:42'),
(1233, 15, NULL, -1, '2015-01-15 11:25:43', '2015-01-15 11:25:43', '2015-01-15 11:25:43'),
(1234, 15, NULL, -1, '2015-01-15 11:25:43', '2015-01-15 11:25:43', '2015-01-15 11:25:43'),
(1235, 15, NULL, 2, '2015-01-15 11:45:00', '2015-01-15 12:45:00', '2015-01-15 11:29:36'),
(1236, 15, NULL, 1, '2015-01-23 14:00:00', '2015-01-23 15:00:00', '2015-01-15 11:39:29'),
(1237, 15, NULL, -1, '2015-01-15 11:53:07', '2015-01-15 11:53:07', '2015-01-15 11:53:07'),
(1238, 15, NULL, -1, '2015-01-15 11:53:39', '2015-01-15 11:53:39', '2015-01-15 11:53:39'),
(1239, 15, NULL, -1, '2015-01-15 11:54:05', '2015-01-15 11:54:05', '2015-01-15 11:54:05'),
(1240, 15, NULL, -1, '2015-01-15 11:54:06', '2015-01-15 11:54:06', '2015-01-15 11:54:06'),
(1241, 15, NULL, -1, '2015-01-15 11:54:07', '2015-01-15 11:54:07', '2015-01-15 11:54:07'),
(1242, 15, NULL, -1, '2015-01-15 11:54:08', '2015-01-15 11:54:08', '2015-01-15 11:54:08'),
(1243, 15, NULL, -1, '2015-01-15 11:54:08', '2015-01-15 11:54:08', '2015-01-15 11:54:08'),
(1244, 15, NULL, -1, '2015-01-15 11:54:09', '2015-01-15 11:54:09', '2015-01-15 11:54:09'),
(1245, 15, NULL, -1, '2015-01-15 11:54:09', '2015-01-15 11:54:09', '2015-01-15 11:54:09'),
(1246, 15, NULL, -1, '2015-01-15 11:54:10', '2015-01-15 11:54:10', '2015-01-15 11:54:10'),
(1247, 15, NULL, -1, '2015-01-15 11:54:19', '2015-01-15 11:54:19', '2015-01-15 11:54:19'),
(1248, 15, NULL, -1, '2015-01-15 11:54:20', '2015-01-15 11:54:20', '2015-01-15 11:54:20'),
(1249, 15, NULL, -1, '2015-01-15 11:54:20', '2015-01-15 11:54:20', '2015-01-15 11:54:20'),
(1250, 15, NULL, -1, '2015-01-15 11:54:21', '2015-01-15 11:54:21', '2015-01-15 11:54:21'),
(1251, 15, NULL, -1, '2015-01-15 11:54:21', '2015-01-15 11:54:21', '2015-01-15 11:54:21'),
(1252, 15, NULL, -1, '2015-01-15 11:54:22', '2015-01-15 11:54:22', '2015-01-15 11:54:22'),
(1253, 15, NULL, -1, '2015-01-15 11:54:22', '2015-01-15 11:54:22', '2015-01-15 11:54:22'),
(1254, 15, NULL, -1, '2015-01-15 11:54:52', '2015-01-15 11:54:52', '2015-01-15 11:54:52'),
(1255, 15, NULL, -1, '2015-01-15 11:54:53', '2015-01-15 11:54:53', '2015-01-15 11:54:53'),
(1256, 15, NULL, -1, '2015-01-15 11:54:54', '2015-01-15 11:54:54', '2015-01-15 11:54:54'),
(1257, 15, NULL, -1, '2015-01-15 11:54:55', '2015-01-15 11:54:55', '2015-01-15 11:54:55'),
(1258, 15, NULL, -1, '2015-01-15 11:55:37', '2015-01-15 11:55:37', '2015-01-15 11:55:37'),
(1259, 15, NULL, -1, '2015-01-15 11:55:38', '2015-01-15 11:55:38', '2015-01-15 11:55:38'),
(1260, 15, NULL, -1, '2015-01-15 11:55:38', '2015-01-15 11:55:38', '2015-01-15 11:55:38'),
(1261, 15, NULL, -1, '2015-01-15 11:55:39', '2015-01-15 11:55:39', '2015-01-15 11:55:39'),
(1262, 15, NULL, -1, '2015-01-15 11:55:40', '2015-01-15 11:55:40', '2015-01-15 11:55:40'),
(1263, 15, NULL, -1, '2015-01-15 11:55:41', '2015-01-15 11:55:41', '2015-01-15 11:55:41'),
(1264, 15, NULL, -1, '2015-01-15 11:55:47', '2015-01-15 11:55:47', '2015-01-15 11:55:47'),
(1267, 15, NULL, -1, '2015-01-15 16:37:10', '2015-01-15 16:37:10', '2015-01-15 16:37:10'),
(1269, 15, NULL, -1, '2015-01-15 16:44:19', '2015-01-15 16:44:19', '2015-01-15 16:44:19'),
(1271, 15, 71, -1, '2015-01-15 19:27:45', '2015-01-15 19:27:45', '2015-01-15 19:27:45'),
(1272, 15, 71, -1, '2015-01-15 19:29:39', '2015-01-15 19:29:39', '2015-01-15 19:29:39'),
(1273, 15, 71, -1, '2015-01-15 19:35:36', '2015-01-15 19:35:36', '2015-01-15 19:35:36'),
(1274, 15, 71, -1, '2015-01-15 19:35:41', '2015-01-15 19:35:41', '2015-01-15 19:35:41'),
(1275, 157, 71, -1, '2015-01-15 19:37:08', '2015-01-15 19:37:08', '2015-01-15 19:37:08'),
(1277, 15, 129, 1, '2015-01-29 10:30:00', '2015-01-29 12:15:00', '2015-01-15 19:57:53'),
(1278, 15, 170, 10, '2015-01-21 20:15:00', '2015-01-21 22:15:00', '2015-01-15 19:59:33'),
(1279, 15, 127, 4, '2015-01-20 18:00:00', '2015-01-20 19:00:00', '2015-01-15 20:00:37'),
(1280, 15, 169, 5, '2015-01-15 20:15:00', '2015-01-15 21:15:00', '2015-01-15 20:00:57'),
(1281, 15, 127, 2, '2015-01-28 15:45:00', '2015-01-28 16:45:00', '2015-01-15 20:01:57'),
(1282, 15, 169, 4, '2015-01-21 13:45:00', '2015-01-21 15:15:00', '2015-01-15 20:02:56'),
(1283, 15, 169, 2, '2015-01-27 10:30:00', '2015-01-27 11:30:00', '2015-01-15 20:03:53');

-- --------------------------------------------------------

--
-- Table structure for table `rezervacija_stol`
--

CREATE TABLE IF NOT EXISTS `rezervacija_stol` (
  `id_rezervacija` int(11) NOT NULL,
  `id_stol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin2 COLLATE=latin2_croatian_ci;

--
-- Dumping data for table `rezervacija_stol`
--

INSERT INTO `rezervacija_stol` (`id_rezervacija`, `id_stol`) VALUES
(1107, 44),
(1111, 44),
(1125, 44),
(1140, 44),
(1162, 44),
(1236, 44),
(1277, 44),
(1281, 44),
(1283, 44),
(1084, 45),
(1108, 45),
(1235, 45),
(1099, 46),
(1278, 46),
(1280, 51),
(1126, 52),
(1127, 52),
(1279, 52),
(1282, 52);

-- --------------------------------------------------------

--
-- Table structure for table `stolovi`
--

CREATE TABLE IF NOT EXISTS `stolovi` (
`id_stol` int(11) NOT NULL,
  `rbr_stol` int(11) NOT NULL,
  `id_ugostitelj` int(11) NOT NULL DEFAULT '0',
  `kapacitet` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `stolovi`
--

INSERT INTO `stolovi` (`id_stol`, `rbr_stol`, `id_ugostitelj`, `kapacitet`) VALUES
(11, 1, 15, 6),
(19, 2, 15, 4),
(44, 12, 15, 2),
(45, 17, 15, 2),
(46, 37, 15, 10),
(47, 43, 15, 5),
(49, 42, 15, 2),
(50, 11, 15, 14),
(51, 22, 15, 5),
(52, 18, 15, 4),
(59, 1, 121, 4),
(60, 1, 141, 4),
(61, 11, 141, 14),
(62, 17, 141, 2),
(63, 12, 141, 2),
(64, 43, 141, 5),
(65, 39, 141, 5),
(66, 9, 121, 4),
(67, 12, 121, 2),
(68, 13, 121, 2),
(69, 2, 121, 4),
(70, 3, 121, 4),
(71, 4, 121, 4),
(72, 1, 142, 12),
(73, 2, 142, 2),
(74, 3, 142, 2),
(75, 4, 142, 2),
(76, 5, 142, 2),
(77, 6, 142, 4),
(78, 7, 142, 4),
(79, 8, 142, 4),
(80, 9, 142, 4),
(81, 10, 142, 8),
(93, 1, 154, 2),
(94, 2, 154, 2),
(95, 7, 154, 4),
(96, 8, 154, 4),
(97, 9, 154, 4),
(98, 10, 154, 4),
(99, 3, 154, 2),
(100, 4, 154, 4),
(101, 5, 154, 4),
(102, 6, 154, 4),
(103, 1, 157, 12),
(104, 2, 157, 2),
(105, 3, 157, 2),
(106, 4, 157, 2),
(107, 5, 157, 2),
(108, 6, 157, 4),
(109, 7, 157, 4),
(110, 8, 157, 4),
(111, 9, 157, 4),
(112, 10, 157, 8);

-- --------------------------------------------------------

--
-- Table structure for table `ugostitelj`
--

CREATE TABLE IF NOT EXISTS `ugostitelj` (
  `id_ugostitelj` int(11) NOT NULL,
  `ime_restoran` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci NOT NULL,
  `adresa` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci NOT NULL,
  `email` varchar(256) NOT NULL,
  `opis` varchar(2048) DEFAULT NULL,
  `vrste_restoran` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci DEFAULT NULL,
  `url_slike_lokala` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci DEFAULT NULL,
  `url_slike_stolova` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci DEFAULT NULL,
  `id_dobavljac` int(11) NOT NULL DEFAULT '0',
  `prihvacen_admin` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ugostitelj`
--

INSERT INTO `ugostitelj` (`id_ugostitelj`, `ime_restoran`, `adresa`, `email`, `opis`, `vrste_restoran`, `url_slike_lokala`, `url_slike_stolova`, `id_dobavljac`, `prihvacen_admin`) VALUES
(15, 'Meso zauvijek', 'Mesna ulica 6', 'andrea.gradecak@gmail.com', 'U ugodnoj atmosferi u&#382;ivajte u mirisu i okusu mesnih jela visoke kvalitete.', 'Pe&#269;enjara, Meso, Ro&#353;tilj, Odrezak', '/media/slike/5f4eec29501aa200e4f298c75ec3a8d7344f9f5a.jpg', '/media/slike/4b91896a3ba63b5e9cad69c4ceb9226b84581edc.jpg', 0, 1),
(72, 'Vrela supa', 'Gornja donja 32', '', NULL, NULL, 'http://poseidon-r.com/image/data/plotesuese/restaurant.jpeg', '', 0, 1),
(119, 'Pizzeria Davidenko', 'Dubrova&#269;ka Avenija 2048', 'pDavidenko@edu.hr', 'Na&#353;a pizzeria nudi sve &#353;to &#382;elite. Kad je Pizza dobra, dan je bolji!', 'pizzeria', '/media/slike/0950531d6463fe7389a292abff890913e64e998e.jpg', '/media/slike/f8a3ebbeb757f86e27ba97b4fa63e7be19d43200.jpg', 0, 1),
(120, 'Davidovi &#263;evapi', 'Ulica Kebaba 4096', 'dg@com', '&#262;evapi mali i veliki. I srednji. &#352;i&#353; &#263;evapi za one jako gladne.', NULL, '/media/slike/d94656377de2c20aea77adfb08f2026f6f53619c.jpg', NULL, 0, 0),
(121, 'Slavonika', 'Ilica 30', 'nikajukic@gmail.com', 'Pizza Slavonika novi je catering smje&#353;ten u samom centru Zagreba. Raznovrsna ponuda pizza, salata i sendvi&#269;a dodatno je oboga&#263;ena pizzama s varijacijama na temu Slavonije.', 'pizzeria', '/media/slike/7281bfd5dc2ffe33f59cbef9bfa14a70be638ce8.jpg', '/media/slike/955d03155cb3448ef457b3d950b1047638617475.jpg', 0, 1),
(141, 'Zmajevo gnijezdo', 'Zmajevci 11', 'zmaj@zmaj.me', 'Zmajevo gnijezdo pru&#382;a smje&#353;teno je u Zmajevcima, a osim stalne ponude jela od mesa, ro&#353;tilja, salata, pala&#269;inki, tjestenina i ri&#382;ota, tu je i svakodnevna raznolika ponuda dnevnih gableca po povoljnim cijenama.', 'Talijanski, meksi&#269;ki, ruski', '/media/slike/b96fa76b57b0a8fb87500a197c1b5392cafd2f59.jpg', '/media/slike/4b91896a3ba63b5e9cad69c4ceb9226b84581edc.jpg', 0, 1),
(142, 'Skromni krumpir', 'Ulica krumpira 14', 'andrea.gradecak@gmail.com', 'Isprobajte najbolja jela s krumpirom, va&#353;om omiljenom namirnicom.', 'Krumpir, Doma&#263;a kuhinja', '/media/slike/0ede47687ff72b6dec27cabc849b1824c713036a.jpg', '/media/slike/cc363e3d65771aa54f43b3c980d1a73195103831.png', 0, 1),
(154, 'Sombrero', 'Meksi&#269;ka 93', 'nikajukic@gmail.com', 'Restoran Sombrero meksi&#269;ki je restoran u srcu Zagreba, u Meksi&#269;koj 93.\r\nUgodan ambijent, ljubazno osoblje i izvrsni specijaliteti meksi&#269;ke kuhinje nikoga ne&#263;e ostaviti ravnodu&#353;nim.\r\nDo&#273;ite i uvjerite se sami!', 'Meksi&#269;ki', '/media/slike/b7cf66d2cc995406f3fda271fada0b379a2ba63a.jpg', '/media/slike/278566b7211b631662099a6a3c345ae1d342ba44.jpg', 0, 1),
(157, '&#352;armer', 'Ulica Ivana &#352;armera 2', 'andrea.gradecak@gmail.com', 'Atraktivna veganska i vegetarijanska jela i jednako atraktivan interijer na&#353;eg restorana &#263;e Vas osvojiti na prvi pogled!', 'Vegetarijanski, Veganski, Slastice', '/media/slike/c776c591184ef7a137098f4148b26917cee7c415.jpg', '/media/slike/cc363e3d65771aa54f43b3c980d1a73195103831.png', 0, 1),
(171, 'Nokturno', 'Skalinska 12', 'nikajukic@gmail.com', NULL, NULL, NULL, NULL, 0, 0),
(172, 'asdas', 'asdasd', 'bobby@gmail.com', NULL, NULL, NULL, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `ugostitelj_za_pretragu`
--
CREATE TABLE IF NOT EXISTS `ugostitelj_za_pretragu` (
`id_ugostitelj` int(11)
,`dio_naziva_restorana` text
,`dio_vrste_restorana` text
,`ocjena_korisnik` decimal(14,4)
,`ocjena_inspekcija` decimal(14,4)
);
-- --------------------------------------------------------

--
-- Table structure for table `vrsta_jela`
--

CREATE TABLE IF NOT EXISTS `vrsta_jela` (
`id_vrsta_jela` int(11) NOT NULL,
  `naziv_vrsta_jela` varchar(64) COLLATE latin2_croatian_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin2 COLLATE=latin2_croatian_ci;

--
-- Dumping data for table `vrsta_jela`
--

INSERT INTO `vrsta_jela` (`id_vrsta_jela`, `naziv_vrsta_jela`) VALUES
(1, 'Predjelo'),
(2, 'Glavno jelo'),
(3, 'Prilozi'),
(4, 'Salata'),
(5, 'Deserti'),
(6, 'Pića');

-- --------------------------------------------------------

--
-- Stand-in structure for view `zauzeti_stolovi_u_vremenu`
--
CREATE TABLE IF NOT EXISTS `zauzeti_stolovi_u_vremenu` (
`id_stol` int(11)
,`kapacitet` int(11)
,`id_ugostitelj` int(11)
,`vrijeme_pocetak_epoch` bigint(11)
,`vrijeme_kraj_epoch` bigint(11)
);
-- --------------------------------------------------------

--
-- Structure for view `ugostitelj_za_pretragu`
--
DROP TABLE IF EXISTS `ugostitelj_za_pretragu`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `ugostitelj_za_pretragu` AS select `ugostitelj`.`id_ugostitelj` AS `id_ugostitelj`,lcase(`ukloni_palatale`(replace(`ugostitelj`.`ime_restoran`,' ',''))) AS `dio_naziva_restorana`,lcase(`ukloni_palatale`(replace(`ugostitelj`.`vrste_restoran`,' ',''))) AS `dio_vrste_restorana`,avg(`recenzijaKorisnik`.`ocjena`) AS `ocjena_korisnik`,avg(`recenzijaInspekcija`.`ocjena`) AS `ocjena_inspekcija` from ((`ugostitelj` left join `recenzija` `recenzijaKorisnik` on(((`recenzijaKorisnik`.`id_ugostitelj` = `ugostitelj`.`id_ugostitelj`) and (isnull(`recenzijaKorisnik`.`id_autor`) or (not(`recenzijaKorisnik`.`id_autor` in (select `korisnik`.`id` from `korisnik` where (`korisnik`.`id_vrsta` = 4)))))))) left join `recenzija` `recenzijaInspekcija` on(((`recenzijaInspekcija`.`id_ugostitelj` = `ugostitelj`.`id_ugostitelj`) and `recenzijaInspekcija`.`id_autor` in (select `korisnik`.`id` from `korisnik` where (`korisnik`.`id_vrsta` = 4))))) where `ugostitelj`.`prihvacen_admin` group by `ugostitelj`.`id_ugostitelj`,`ugostitelj`.`ime_restoran`;

-- --------------------------------------------------------

--
-- Structure for view `zauzeti_stolovi_u_vremenu`
--
DROP TABLE IF EXISTS `zauzeti_stolovi_u_vremenu`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `zauzeti_stolovi_u_vremenu` AS select `rezervacija_stol`.`id_stol` AS `id_stol`,`stolovi`.`kapacitet` AS `kapacitet`,`rezervacija`.`id_ugostitelj` AS `id_ugostitelj`,unix_timestamp(`rezervacija`.`vrijeme_pocetak`) AS `vrijeme_pocetak_epoch`,unix_timestamp(`rezervacija`.`vrijeme_kraj`) AS `vrijeme_kraj_epoch` from ((`rezervacija` join `rezervacija_stol` on((`rezervacija`.`id_rezervacija` = `rezervacija_stol`.`id_rezervacija`))) join `stolovi` on((`rezervacija_stol`.`id_stol` = `stolovi`.`id_stol`)));

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gost`
--
ALTER TABLE `gost`
 ADD PRIMARY KEY (`id_gost`);

--
-- Indexes for table `jelo`
--
ALTER TABLE `jelo`
 ADD PRIMARY KEY (`id_jelo`), ADD KEY `id_vrsta_jela` (`id_vrsta_jela`), ADD KEY `id_restoran` (`id_ugostitelj`);

--
-- Indexes for table `konobar`
--
ALTER TABLE `konobar`
 ADD PRIMARY KEY (`id_konobar`), ADD KEY `id_restoran` (`id_ugostitelj`);

--
-- Indexes for table `korisnik`
--
ALTER TABLE `korisnik`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`korisnicko_ime`), ADD KEY `id_vrsta` (`id_vrsta`);

--
-- Indexes for table `korisnik_vrsta`
--
ALTER TABLE `korisnik_vrsta`
 ADD PRIMARY KEY (`id_vrsta`);

--
-- Indexes for table `nabava`
--
ALTER TABLE `nabava`
 ADD PRIMARY KEY (`id_nabava`), ADD KEY `id_restoran` (`id_ugostitelj`), ADD KEY `nabava_ibfk_2` (`id_dobavljac`);

--
-- Indexes for table `nabava_namirnice`
--
ALTER TABLE `nabava_namirnice`
 ADD PRIMARY KEY (`id_nabava`,`id_namirnica`), ADD KEY `nabava_namirnice_ibfk_2` (`id_namirnica`);

--
-- Indexes for table `namirnica`
--
ALTER TABLE `namirnica`
 ADD PRIMARY KEY (`id_namirnica`), ADD KEY `namirnica_ibfk_1` (`id_ugostitelj`);

--
-- Indexes for table `narudzba`
--
ALTER TABLE `narudzba`
 ADD PRIMARY KEY (`id_jelo`,`id_rezervacija`), ADD KEY `narudzba_ibfk_1` (`id_rezervacija`);

--
-- Indexes for table `ponuda`
--
ALTER TABLE `ponuda`
 ADD PRIMARY KEY (`id_ponude_jela`,`id_jelo`), ADD KEY `ponuda_ibfk_2` (`id_jelo`);

--
-- Indexes for table `ponuda_jela`
--
ALTER TABLE `ponuda_jela`
 ADD PRIMARY KEY (`id_ponuda`), ADD KEY `ponuda_jela_ibfk_1` (`id_ugostitelj`);

--
-- Indexes for table `radno_vrijeme`
--
ALTER TABLE `radno_vrijeme`
 ADD PRIMARY KEY (`id_ugostitelj`,`id_dan`);

--
-- Indexes for table `recenzija`
--
ALTER TABLE `recenzija`
 ADD PRIMARY KEY (`id_recenzija`), ADD KEY `id_autor` (`id_autor`), ADD KEY `recenzija_ibfk_1` (`id_ugostitelj`);

--
-- Indexes for table `rezervacija`
--
ALTER TABLE `rezervacija`
 ADD PRIMARY KEY (`id_rezervacija`), ADD KEY `id_gost` (`id_gost`), ADD KEY `rezervacija_ibfk_1` (`id_ugostitelj`);

--
-- Indexes for table `rezervacija_stol`
--
ALTER TABLE `rezervacija_stol`
 ADD PRIMARY KEY (`id_rezervacija`,`id_stol`), ADD KEY `rezervacija_stol_ibfk_1` (`id_stol`);

--
-- Indexes for table `stolovi`
--
ALTER TABLE `stolovi`
 ADD PRIMARY KEY (`id_stol`), ADD UNIQUE KEY `rbr_stol` (`rbr_stol`,`id_ugostitelj`), ADD KEY `id_restoran` (`id_ugostitelj`);

--
-- Indexes for table `ugostitelj`
--
ALTER TABLE `ugostitelj`
 ADD PRIMARY KEY (`id_ugostitelj`);

--
-- Indexes for table `vrsta_jela`
--
ALTER TABLE `vrsta_jela`
 ADD PRIMARY KEY (`id_vrsta_jela`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jelo`
--
ALTER TABLE `jelo`
MODIFY `id_jelo` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=104;
--
-- AUTO_INCREMENT for table `korisnik`
--
ALTER TABLE `korisnik`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=174;
--
-- AUTO_INCREMENT for table `nabava`
--
ALTER TABLE `nabava`
MODIFY `id_nabava` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=51;
--
-- AUTO_INCREMENT for table `namirnica`
--
ALTER TABLE `namirnica`
MODIFY `id_namirnica` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=46;
--
-- AUTO_INCREMENT for table `ponuda_jela`
--
ALTER TABLE `ponuda_jela`
MODIFY `id_ponuda` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `recenzija`
--
ALTER TABLE `recenzija`
MODIFY `id_recenzija` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=70;
--
-- AUTO_INCREMENT for table `rezervacija`
--
ALTER TABLE `rezervacija`
MODIFY `id_rezervacija` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1284;
--
-- AUTO_INCREMENT for table `stolovi`
--
ALTER TABLE `stolovi`
MODIFY `id_stol` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=113;
--
-- AUTO_INCREMENT for table `vrsta_jela`
--
ALTER TABLE `vrsta_jela`
MODIFY `id_vrsta_jela` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `gost`
--
ALTER TABLE `gost`
ADD CONSTRAINT `gost_ibfk_1` FOREIGN KEY (`id_gost`) REFERENCES `korisnik` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jelo`
--
ALTER TABLE `jelo`
ADD CONSTRAINT `jelo_ibfk_1` FOREIGN KEY (`id_vrsta_jela`) REFERENCES `vrsta_jela` (`id_vrsta_jela`) ON DELETE CASCADE,
ADD CONSTRAINT `jelo_ibfk_2` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE;

--
-- Constraints for table `konobar`
--
ALTER TABLE `konobar`
ADD CONSTRAINT `konobar_ibfk_2` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE,
ADD CONSTRAINT `konobar_ibfk_1` FOREIGN KEY (`id_konobar`) REFERENCES `korisnik` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `korisnik`
--
ALTER TABLE `korisnik`
ADD CONSTRAINT `korisnik_ibfk_1` FOREIGN KEY (`id_vrsta`) REFERENCES `korisnik_vrsta` (`id_vrsta`);

--
-- Constraints for table `nabava`
--
ALTER TABLE `nabava`
ADD CONSTRAINT `nabava_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE,
ADD CONSTRAINT `nabava_ibfk_2` FOREIGN KEY (`id_dobavljac`) REFERENCES `korisnik` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nabava_namirnice`
--
ALTER TABLE `nabava_namirnice`
ADD CONSTRAINT `nabava_namirnice_ibfk_2` FOREIGN KEY (`id_namirnica`) REFERENCES `namirnica` (`id_namirnica`),
ADD CONSTRAINT `nabava_namirnice_ibfk_1` FOREIGN KEY (`id_nabava`) REFERENCES `nabava` (`id_nabava`) ON DELETE CASCADE;

--
-- Constraints for table `namirnica`
--
ALTER TABLE `namirnica`
ADD CONSTRAINT `namirnica_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE;

--
-- Constraints for table `narudzba`
--
ALTER TABLE `narudzba`
ADD CONSTRAINT `narudzba_ibfk_1` FOREIGN KEY (`id_rezervacija`) REFERENCES `rezervacija` (`id_rezervacija`) ON DELETE CASCADE,
ADD CONSTRAINT `narudzba_ibfk_2` FOREIGN KEY (`id_jelo`) REFERENCES `jelo` (`id_jelo`) ON DELETE CASCADE;

--
-- Constraints for table `ponuda`
--
ALTER TABLE `ponuda`
ADD CONSTRAINT `ponuda_ibfk_2` FOREIGN KEY (`id_jelo`) REFERENCES `jelo` (`id_jelo`) ON DELETE CASCADE,
ADD CONSTRAINT `ponuda_ibfk_1` FOREIGN KEY (`id_ponude_jela`) REFERENCES `ponuda_jela` (`id_ponuda`) ON DELETE CASCADE;

--
-- Constraints for table `ponuda_jela`
--
ALTER TABLE `ponuda_jela`
ADD CONSTRAINT `ponuda_jela_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE;

--
-- Constraints for table `radno_vrijeme`
--
ALTER TABLE `radno_vrijeme`
ADD CONSTRAINT `radno_vrijeme_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE;

--
-- Constraints for table `recenzija`
--
ALTER TABLE `recenzija`
ADD CONSTRAINT `recenzija_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE,
ADD CONSTRAINT `recenzija_ibfk_2` FOREIGN KEY (`id_autor`) REFERENCES `korisnik` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rezervacija`
--
ALTER TABLE `rezervacija`
ADD CONSTRAINT `rezervacija_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE,
ADD CONSTRAINT `rezervacija_ibfk_2` FOREIGN KEY (`id_gost`) REFERENCES `gost` (`id_gost`) ON DELETE CASCADE;

--
-- Constraints for table `rezervacija_stol`
--
ALTER TABLE `rezervacija_stol`
ADD CONSTRAINT `rezervacija_stol_ibfk_1` FOREIGN KEY (`id_stol`) REFERENCES `stolovi` (`id_stol`) ON DELETE CASCADE,
ADD CONSTRAINT `rezervacija_stol_ibfk_2` FOREIGN KEY (`id_rezervacija`) REFERENCES `rezervacija` (`id_rezervacija`) ON DELETE CASCADE;

--
-- Constraints for table `stolovi`
--
ALTER TABLE `stolovi`
ADD CONSTRAINT `stolovi_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `ugostitelj` (`id_ugostitelj`) ON DELETE CASCADE;

--
-- Constraints for table `ugostitelj`
--
ALTER TABLE `ugostitelj`
ADD CONSTRAINT `ugostitelj_ibfk_1` FOREIGN KEY (`id_ugostitelj`) REFERENCES `korisnik` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
