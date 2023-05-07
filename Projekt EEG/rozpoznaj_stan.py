# program, ktory rozpoznaje epoke na podstawie folderu "analiza"
# wysyla obecny stan do tego samego folderu jako plik obecny_stan.txt

#*************************************************************************

import numpy as np
import funkcje_orly as fnk
from funkcje_orly import KNN, Strumien
from time import time
import os

#*************************************************************************

# parametry do ustawienia

katalog = r"C:\Users\sheil\Desktop\S4\ORŁY\PROJEKT_GŁÓWNY\output_data\Rejestracja_test\NIKT\kanal1,3stany"

t_move = 0.2
# przesuniecie ramki 

#*************************************************************************

# odczyt danych z pliku cz.1

if os.path.isdir(katalog) == 0:
    raise ValueError("Podany katalog nie istnieje")
if os.path.isdir(katalog + "\\analiza") == 0:
    raise ValueError("Brak folderu analiza. Przeprowadz nauke klasyfikatora")
opis = np.loadtxt(katalog + "\\opis_eksperymentu.txt",delimiter=";",dtype=str)
typ = opis[17][1]
numery_wybranych_kanalow = fnk.napis2macierz(opis[15][1],typ=int)
if typ == "EEG":
    urzadzenie = opis[8][1]

#*************************************************************************

# podłączenie streamow

# klawiatura
klawiatura = Strumien("klawiatura")

# EEG
if typ == "audio":
    EEG = Strumien("audio",numery_wybranych_kanalow=numery_wybranych_kanalow) 
elif typ == "EEG": 
    EEG = Strumien("EEG",urzadzenie=urzadzenie,numery_wybranych_kanalow=numery_wybranych_kanalow) 
else:
    raise ValueError("Niepoprawny typ. Dozwolone: 'audio','EEG'")

#*************************************************************************

# odczyt danych z pliku cz.2

punkty_nauka = fnk.plik2macierz(katalog + "\\analiza\\punkty.txt") 
# punkty do uczenia klasyfikatora
kategorie_nauka = fnk.plik2macierz(katalog + "\\analiza\\kategorie.txt",dtype=str)
kategorie_nauka_numeric = fnk.plik2macierz(katalog + "\\analiza\\kategorie_numeric.txt",dtype=int)
slownik_kategorie = fnk.plik2slownik(katalog + "\\analiza\\slownik_kategorii.txt")
przedzialy_f = fnk.plik2macierz(katalog + "\\analiza\\przedzialy.txt",dtype=int,pionowa=False)
ilosc_cech = len(przedzialy_f)
k = fnk.plik2wartosc(katalog + "\\analiza\\liczba_sasiadow.txt",dtype=int)

# przygotowanie KNN
knn = KNN(k)
knn.fit(punkty_nauka,kategorie_nauka_numeric)

#*************************************************************************

# parametry

t_pierwsze = 1 # pierwsza ramka
stan = -1 # obecny stan
licznik = 0

#*************************************************************************

print(' =================== ROZPOCZĘCIE EKSPERYMENTU =================== ');
start = time()

sygnal = EEG.pobierz_probki(ilosc_sekund = t_pierwsze)

punkty = []  

punkt = []
# punkt sklada sie z Pavg wszystkich kanalow
for j in range(EEG.ilosc_wybranych_kanalow):
    kanal = sygnal[:,j]
    
    f,A = fnk.widmo_amplitudowe(kanal,EEG.fs)
    f = np.array(f)
    A = np.array(A)
    
    for p in range(ilosc_cech):
    # petla po wybranych pasmach czestotliwosci
        pasmo = A[(f >= przedzialy_f[p][0]) & (f <= przedzialy_f[p][1])]
        Pavg = np.mean(pasmo**2) 
        # srednia moc w pasmie
        punkt.append(Pavg)
        
punkty.append(punkt)
poprzedni_stan = int(knn.predict(punkt))

klawisz = None
plik = katalog + "\\analiza\\obecny_stan.txt"

while klawisz == None or str(klawisz[0]) != "ESCAPE pressed":
    
    nowy_sygnal = EEG.pobierz_probki(ilosc_sekund=t_move)
    ilosc_nowych_probek = len(nowy_sygnal)
    
    if ilosc_nowych_probek != 0:
        sygnal = np.vstack((sygnal,nowy_sygnal)) # dodajemy nowe
        sygnal = sygnal[ilosc_nowych_probek:] # ucinamy poczatek
        
        punkt = []
        # punkt sklada sie z Pavg wszystkich kanalow
        for j in range(EEG.ilosc_wybranych_kanalow):
            kanal = sygnal[:,j]
            
            f,A = fnk.widmo_amplitudowe(kanal,EEG.fs)
            f = np.array(f)
            A = np.array(A)
            
            for p in range(ilosc_cech):
            #petla po wybranych pasmach czestotliwosci
                pasmo = A[(f >= przedzialy_f[p][0]) & (f <= przedzialy_f[p][1])]
                Pavg = np.mean(pasmo**2) 
                # srednia moc w pasmie
                punkt.append(Pavg)
                
        punkty.append(punkt)
        chwilowy_stan = int(knn.predict(punkt))
        
        # ustalamy stan
        if poprzedni_stan == chwilowy_stan:
            licznik += 1
        else:
            licznik = 0
        if licznik >= 2 and chwilowy_stan != stan:
            # zmiana stanu
            stan = chwilowy_stan
            licznik = 0
            print(slownik_kategorie[stan])
            f = open(plik,"w")
            f.write(str(stan))
            f.close()
        poprzedni_stan = chwilowy_stan
    
    klawisz = klawiatura.pobierz_probke()
    
print(' =================== KONIEC DZIALANIA PROGRAMU =================== ');

fnk.usun_plik(plik)

print("Czas trwania eksperymentu:",time() - start)


