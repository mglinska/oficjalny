# program, ktory odczytuje sygnal EEG z wybranego podfolderu "output_data"
# klasyfikuje dane wedlug zapisanych epok
# opcjonalnie zapisuje dane klasyfikacji do folderu "analiza"

#*************************************************************************

import numpy as np
import pandas as pd
import funkcje_orly as fnk
import matplotlib.pyplot as plt
from funkcje_orly import KNN
import os

#*************************************************************************

# parametry do ustawienia

katalog = r"C:\Users\sheil\Desktop\S4\ORŁY\PROJEKT_GŁÓWNY\output_data\Rejestracja_test\NIKT\kanal1,3stany"

# pasma czestotliwosci, ktorych chcemy uzyc przy EEG 
# nie ma znaczenia, gdy typ to Audio
use_alfa = 1
use_beta = 1
use_gamma = 0
use_delta = 0
use_theta = 0

wycieto = 1.2
# ile sekund wycinamy z poczatku kazdej epoki
# dopasuj ten parametr tak, zeby uzyskac jak najwyzsza dokladnosc

k = 3
# liczba sasiadow 
# dopasuj ten parametr tak, zeby uzyskac jak najwyzsza dokladnosc

zapis_do_pliku = 1
# zapis danych wynikowych do folderu "analiza"
# zapis jest konieczny, aby uruchomic program "rozpoznaj_stan"
# zalecane: zapis_do_pliku = 1

draw = 1
if draw == 1:
    draw_sygnal = 1
    draw_kategorie = 1
    draw_widmo = 1
# rysowanie wykresow

#*************************************************************************

if os.path.isdir(katalog) == 0:
    raise ValueError("Podany katalog nie istnieje")

# wczytanie danych z plikow dotyczacych eksperymentu

print("Odczyt danych z pliku...")

sygnal = fnk.plik2macierz(katalog + "\\EEG_signal.txt",delimiter=",")
# ilosc probek x ilosc kanalow

t = np.loadtxt(katalog + "\\EEG_time.txt")

events = pd.read_csv(katalog + "\\Events.txt")
ilosc_epok = len(events) - 2 # bez "begin scenario" i "end scenario"

opis = np.loadtxt(katalog + "\\opis_eksperymentu.txt",delimiter=";",dtype=str)
typ = opis[17][1]
if typ != "audio" and typ != "EEG":
    raise ValueError("W opisie znajduje sie niepoprawny typ. Dozwolone: 'EEG','audio'")
ilosc_kanalow = int(opis[12][1]) # ilosc wybranych kanalow
fs = float(opis[10][1]) # czestotliwosc probkowania

#*************************************************************************

# ustawianie parametrow

if ilosc_kanalow != sygnal.shape[1]:
    raise ValueError("Ilosc kanalow podana w opisie nie zgadza sie z wczytana macierza danych")
if ilosc_epok == 0:
    raise ValueError("Brak epok do analizy")
ts = 1 / fs # czas trwania jednej probki
delta_t = 1 # ilosc sekund w jednym przedziale (wycinku)

# przedzialy czestotliwosci wziete pod uwage przy cechach 
if typ == "audio":
    przedzialy_f = [[1000,10000]]
elif typ == "EEG":
    przedzial_alfa = [8,13]
    przedzial_beta = [13,30]
    przedzial_gamma = [30,50]
    przedzial_delta = [0,4]
    przedzial_theta = [4,8]
    przedzialy_f = []
    if use_alfa == 1:
        przedzialy_f.append(przedzial_alfa)
    if use_beta == 1:
        przedzialy_f.append(przedzial_beta)
    if use_gamma == 1:
        przedzialy_f.append(przedzial_gamma)
    if use_delta == 1:
        przedzialy_f.append(przedzial_delta)
    if use_theta == 1:
        przedzialy_f.append(przedzial_theta)

ilosc_cech = len(przedzialy_f)

#*************************************************************************

print("Wyznaczanie cech...")

# przygotowanie punktow i kategorii
# wycinamy po kolei (delta_t) sekund

punkty = []
# (ilosc_epok * ilosc przedzialow w epoce) x (ilosc_kanalow * ilosc_cech)
# jeden punkt sklada sie z Pavg wszystkich kanalow
# kanal pierwszy odpowiada (ilosc_cech) pierwszym kolumnom
# kazdy punkt ma przyporzadkowana kategorie
kategorie = []
# (ilosc_epok * ilosc przedzialow w epoce) x 1

for e in range(ilosc_epok):
# petla po epokach
    t_0 = events["latency"][e + 1] + wycieto
    # wycinamy malo wiarygodny fragment poczatkowy
    t_end = events["latency"][e + 2]
    
    for s in np.arange(t_0,t_end,delta_t):
    # petla po kolejnych przedzialach czasowych
        t1 = s # czas poczatkowy danego wycinka
        t2 = s + delta_t # czas koncowy danego wycinka
        if t2 > t_end:
            t2 = t_end
            if t2 - t1 < 0.5 * delta_t:
                continue
        # jesli czas wycinka przekracza epoke, ucinamy
        # sprawdzamy, czy wycinek jest wystarczajaco duzy,
        # zeby wziac go pod uwage
        wiersz_logiczny = (t >= t1) & (t <= t2) #  t1 <= t <= t2
        #  np. delta_t = 1, t_0 = 0.5 ->
        #  pierwszy przedzial: 0.5 <= t <= 1.5
            
        #*******************************************************************
        
        punkt = []
        #punkt sklada sie z Pavg wszystkich kanalow w przedziale [t1,t2]
        
        for j in range(ilosc_kanalow):
            kanal = sygnal[:,j]
            wyciety_sygnal = kanal[wiersz_logiczny]
            # probki sygnalu zebrane w czasie [t1,t2]
        
            f, A = fnk.widmo_amplitudowe(wyciety_sygnal,fs)
            f = np.array(f)
            A = np.array(A)
            
            for p in range(ilosc_cech):
            # petla po wybranych pasmach czestotliwosci
                pasmo = A[(f >= przedzialy_f[p][0]) & (f <= przedzialy_f[p][1])]
                Pavg = np.mean(pasmo**2) 
                # srednia moc w pasmie
                punkt.append(Pavg)
        
        punkty.append(punkt)
        kategorie.append(events["action"][e + 1])
        
punkty = fnk.nowa_macierz_2D(punkty)
kategorie = fnk.nowa_macierz_2D(kategorie,pionowa=True)
kategorie_numeric = np.unique(kategorie,return_inverse=True)[1].tolist()
# stringi => unikalne inty
slownik_kategorie = fnk.kategorie2slownik(kategorie,kategorie_numeric)

#*************************************************************************

# analiza

print("Analiza cech...")
knn = KNN(k)
knn.fit(punkty,kategorie_numeric)
dokladnosc = fnk.analiza_dokladnosci(punkty, kategorie_numeric, k)
print("Zakonczono analize")

#*************************************************************************

if draw == 1:
    
    print("Tworzenie wykresow...")
    
    if draw_sygnal == 1:
        fnk.wykres_EEG(t,sygnal) 
        # wykres wszystkich kanalow
        
    if draw_kategorie == 1 and len(punkty[0]) >= 2:
        plt.figure()
        plt.scatter(punkty[:,0],punkty[:,1],c=kategorie_numeric,cmap=plt.get_cmap("cool"),s=10)
        # wizualizacja kategorii dla punktow pierwszego kanalu
        plt.colorbar()
        plt.title("Kategorie punktow dla kanalu 1\n" + str(slownik_kategorie))
        plt.xlabel("Srednia moc pasma 1")
        plt.ylabel("Srednia moc pasma 2")
    
    if draw_widmo == 1:
        f,A = fnk.widmo_amplitudowe(sygnal[:,0],fs)
        plt.figure()
        plt.plot(f,A)
        plt.title("Widmo amplitudowe dla kanalu 1")
        plt.xlabel("czestotliwosc f [Hz]")
        plt.ylabel("amplituda A")
    
#*************************************************************************

if zapis_do_pliku == 1:
    
    folder = katalog + "\\analiza"
    fnk.stworz_folder(folder)
    
    print("Zapis do pliku...")
    fnk.macierz2plik(folder + "\\punkty.txt",punkty)
    fnk.macierz2plik(folder + "\\kategorie.txt",kategorie)
    fnk.macierz2plik(folder + "\\przedzialy.txt",przedzialy_f)
    fnk.macierz2plik(folder + "\\liczba_sasiadow.txt",[k])
    fnk.slownik2plik(folder + "\\slownik_kategorii.txt",slownik_kategorie)
    fnk.macierz2plik(folder + "\\kategorie_numeric.txt",kategorie_numeric)
    
print(" =================== KONIEC DZIALANIA PROGRAMU =================== ")    
print("Dokladnosc:",dokladnosc) 
