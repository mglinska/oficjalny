# program, ktory zapisuje dane EEG do folderu "output_data"
# odczytuje takze zdarzenia zewnetrzne pod postacia pliku "stymulacja.txt"
# aby wysylac pliki "stymulacja.txt", uruchom program "zdarzenia_zewnetrzne"

#*************************************************************************

from time import time  # tic toc
start_programu = time()
import datetime as d  # obecna data
import os # komendy terminalowe np. usunięcie pliku
import numpy as np  # macierze
import pandas as pd # do obsługi csv
import funkcje_orly as fnk # wlasne, oddzielone od programu funkcje
from funkcje_orly import Strumien

#*************************************************************************

# parametry do ustawienia

# typ = "EEG"
typ = "audio"

# urzadzenie = "MITSAR"
urzadzenie = "OPENBCI"

# numery_wybranych_kanalow = [1,2] 
# numery_wybranych_kanalow = [7,8]
numery_wybranych_kanalow = [] # czyli uwzgledniamy wszystkie kanaly  

nazwy_kanalow_glowa = ["zgodne z urzadzeniem"]
# nazwy_kanalow_glowa = ["Fp1","Fp2","FP888"]

nazwa_eksperymentu = "Rejestracja_test"
uzytkownik_imie_nazwisko = "NIKT"
uzytkownik_wiek = "34"
uzytkownik_recznosc = "lewa"
uzytkownik_wzrok = "ok"
uwagi = "brak"

plik_zdarzenie_zewnetrzne = "stymulacja.txt"
# jest to plik, który będzie dostarczany nam przez inny program
# będą w nim zapisywane zdarzenia zewnętrzne takie jak pojawienie się strzałki na ekranie

klawisz_konczacy = "ESCAPE pressed" # klawisz kończący eksperyment
clock_frequency = 4
dlugosc_zbioru_temp = 3 

# katalog_output = "output_data\\" + str(nazwa_eksperymentu) + "\\" + str(uzytkownik_imie_nazwisko)
katalog_output = "output_data"

katalog_temp = katalog_output + "\\temp"
# ścieżka folderów dla przyszłych plików tymczasowych przechowujących zebrany sygnał EEG

czas_rozpoczecia_eksperymentu = str(d.datetime.now())    
# data w formie "2021-01-09 22:56:27.327276"
czas_rozpoczecia_zapisu_danych_EEG = ""
wzmacniacz = urzadzenie
signal_header_fs = 0

#*************************************************************************

# podłączenie streamow

# klawiatura
klawiatura = Strumien("klawiatura")

# EEG
if typ == "audio":
    EEG = Strumien("audio", numery_wybranych_kanalow = numery_wybranych_kanalow) 
elif typ == "EEG": 
    EEG = Strumien("EEG", urzadzenie, numery_wybranych_kanalow) 
else:
    raise ValueError("Niepoprawny typ. Dozwolone: 'audio','EEG'")
    
#*************************************************************************

# katalogi

# jeżeli katalog temp istnieje, chcemy go usunac, a potem stworzyc od nowa
fnk.stworz_folder(katalog_temp)  

# jezeli plik zewnatrzny istnieje, usuwamy go 
fnk.usun_plik(plik_zdarzenie_zewnetrzne)
    
#************************************************************************* 

# kanaly

nazwy_kanalow_urzadzenie = EEG.nazwy_kanalow

if nazwy_kanalow_glowa[0] != "zgodne z urzadzeniem": # podano wlasne nazwy_kanalow_glowa
    if len(nazwy_kanalow_glowa) != len(EEG.numery_wybranych_kanalow):
        raise ValueError("LICZBA KANALOW NA GLOWIE JEST ROZNA OD WYBRANEJ LICZBY KANALOW NA URZADZENIU")
else: 
    nazwy_kanalow_glowa = nazwy_kanalow_urzadzenie
    
#*************************************************************************

EEG.pobierz_probki()
# zeby oczyscic sygnal

czas_rozpoczecia_zapisu_danych_EEG_duration = d.datetime.now()
ti = 0
zdarzenia = [['0.000','flag','none','begin scenario','none','none','Matlab','none','none','none','none','none','none','none','none','none','none','none']]
        
startLSL = time();  # odpowiednik Matlabowego "tic"
# koniec (odpowiednik Matlabowego "toc"): minelo_czasu = time() - startLSL

zakonczProgram = False
ilosc_plikow_temp = 0

print(' =================== ROZPOCZĘCIE EKSPERYMENTU =================== ');

start_petla = time()

while zakonczProgram == False:
    
    if time() - startLSL >= 1 / clock_frequency:  
    # minelo_czasu = time() - startLSL    odpowiednik toc()
    # pętla uruchamia się co jakiś czas
    # np. gdy clock_frequency = 4, 
    # pobieramy nowe probki 4 razy w ciagu sekundy 
        EEG_sygnal_nowe = EEG.pobierz_probki()
        # pobieramy dane z EEG
        
        ilosc_nowych_probek = len(EEG_sygnal_nowe)
        
        if ilosc_nowych_probek != 0:
            # obrobka probek
            if wzmacniacz == "OPENBCI":
                EEG_sygnal_nowe = EEG_sygnal_nowe * (-1)
                
            # utworzenie wektora czasu
            EEG_czas_nowe = []
            for i in range(ilosc_nowych_probek):
                EEG_czas_nowe.append(ti)
                ti += EEG.ts 
                # przechodzimy do kolejnej probki
                # do aktualnej probki czasu (ti) dodajemy czas trwania jednej probki (ts)
        
            # zapis danych EEG (probki,czasy) do plikow tymczasowych
            if ilosc_nowych_probek >= dlugosc_zbioru_temp * signal_header_fs:
            # o co chodzi w tym ifie?
                ilosc_plikow_temp += 1
        
                nazwa_pliku = katalog_temp + "\\temp_dane_sygnal" + str(ilosc_plikow_temp) + ".txt"
                fnk.macierz2plik(nazwa_pliku,EEG_sygnal_nowe)
        
                nazwa_pliku = katalog_temp + "\\temp_dane_czas" + str(ilosc_plikow_temp) + ".txt"
                fnk.macierz2plik(nazwa_pliku,EEG_czas_nowe)
                
            #**********************************************************************
        
            if os.path.isfile(plik_zdarzenie_zewnetrzne) == 1:
            # jezeli z zewnatrz zostal dostarczony plik ze zdarzeniem
        
                # wczytujemy zdarzenie z pliku
                zdarzenie_zewnetrzne = np.loadtxt(plik_zdarzenie_zewnetrzne, 
                                                  dtype = str, 
                                                  delimiter = ",")
                # ['20201210213556290' 'none1' 'none2' 'none' 'Aplikacja' 'Emocje' 'Stymulacja.txt' 'none' 'none' 'none' 'none' 'none' 'none' 'none' 'none' 'none' 'none' 'none']
        
                # zanim dodamy to do wektora zdarzen,
                # musimy przeksztalcic '20201210213556290' na latency
                
                if len(zdarzenie_zewnetrzne) == 18:
                # jezeli poprawna dlugosc pliku
                    os.remove(plik_zdarzenie_zewnetrzne) # usuniecie pliku     
        
                    d1 = zdarzenie_zewnetrzne[0]  # 20201210213556290
        
                    if len(d1) == 17:
                    # jesli poprawna dlugosc daty
                        # konwersja na int i rozdzielenie na czesci
                        dt = [int(d1[0:4]),int(d1[4:6]),int(d1[6:8]),int(d1[8:10]),int(d1[10:12]),int(d1[12:14]),int(d1[14:17])]
                        # efekt: [2020, 12, 10, 21, 35, 56, 290]
        
                        # ponizej kombinujemy tak tylko dlatego, ze w danych o zdarzeniach
                        # pochodzacych z pliku zewnetrznego beda podane milisekundy
                        dzisiaj = d.date.today()  # obiekt klasy "date"
                        # musi byc takie same jak w czas_rozpoczecia_zapisu_danych_EEG_duration
                        czas = d.time(dt[3],dt[4],dt[5],dt[6] * 1000) # obiekt klasy "time"
                        # class datetime.time(hour=0, minute=0, second=0, microsecond=0, tzinfo=None, *, fold=0)
                        # trzeba bylo zamienic milisekundy => microsecond
                        dt_show = d.datetime.combine(dzisiaj,czas) # obiekt klasy "datetime"
        
                        latency_show = fnk.data_na_latency(dt_show,czas_rozpoczecia_zapisu_danych_EEG_duration)
                        zdarzenie_zewnetrzne[0] = latency_show 
                        # zamieniamy stara date na nowa, przeksztalcona na latency
                        
                        zdarzenia.append(zdarzenie_zewnetrzne) 
                        # dodajemy zdarzenie do wektora zdarzen
                        
                        print(zdarzenie_zewnetrzne[3])
                    else:
                        print('niepelna data!', d1, '( dlugosc:', len(d1), ')')
                else:
                    raise ValueError("Niepoprawny plik zewnetrzny")
                    
            #**********************************************************************
            
            # sprawdzamy, czy w wektorze zdarzen nie ma "end scenario"
            if zdarzenia[-1][3] == "end scenario":
                zakonczProgram = True
            else:
                vec1 = klawiatura.pobierz_probke()
                # pobieramy probke z klawiatury
                
                # jesli wcisnieto klawisz konczacy,
                # dodajemy do wektora zdarzen zdarzenie zakonczenia scenariusza
                if vec1 != None and str(vec1[0]) == klawisz_konczacy:
                    zakonczProgram = True
                    latency = fnk.data_na_latency(0, czas_rozpoczecia_zapisu_danych_EEG_duration)
                    zdarzenia.append([latency,'flag','none','end scenario','Keyboard','Keyboard','Matlab','none','none','none','none','none','none','none','none','none','none','none'])

        startLSL = time()
        
czas_eksperymentu = time() - start_petla

#***************************************************************************************************************************

print(" =================== ZAKONCZENIE EKSPERYMENTU =================== ")
print(" =================== TRWA ZAPIS DANYCH =================== ")

# nowy folder REGISTER, w którym umiescimy zebrane wyniki
cz = d.datetime.now()
data_exp = cz.strftime("%Y-%m-%d-%H-%M-%S")  # konwersja datetime => string 
katalog_wyniki = katalog_output + "\\REGISTER_" + str(data_exp) + "_dane_wynikowe"
fnk.stworz_folder(katalog_wyniki)

#****************************************

# "EEG_signal.txt" i "EEG_time.txt"
if ilosc_plikow_temp > 0:

    # wczytanie pierwszego pliku temp
    EEG_sygnal_caly = fnk.plik2macierz(katalog_temp + "\\temp_dane_sygnal1.txt")
    EEG_czas_caly = fnk.plik2macierz(katalog_temp + "\\temp_dane_czas1.txt")
  
    os.remove(katalog_temp + "\\temp_dane_sygnal1.txt")
    os.remove(katalog_temp + "\\temp_dane_czas1.txt")

    # wczytanie pozostalych plikow temp
    for i in range(2,ilosc_plikow_temp + 1):

        temp = fnk.plik2macierz(katalog_temp + "\\temp_dane_sygnal" + str(i) + ".txt")
        EEG_sygnal_caly = np.vstack((EEG_sygnal_caly,temp))

        temp = fnk.plik2macierz(katalog_temp + "\\temp_dane_czas" + str(i) + ".txt")
        EEG_czas_caly = np.vstack((EEG_czas_caly,temp))

        os.remove(katalog_temp + "\\temp_dane_sygnal" + str(i) + ".txt")
        os.remove(katalog_temp + "\\temp_dane_czas" + str(i) + ".txt")
    
    fnk.macierz2plik(katalog_wyniki + "\\EEG_signal.txt",EEG_sygnal_caly,delimiter = ",")
    fnk.macierz2plik(katalog_wyniki + "\\EEG_time.txt",EEG_czas_caly)
    os.rmdir(katalog_temp)

#****************************************

# "Events.txt":
Events = pd.DataFrame(zdarzenia)
Events.columns = ['latency','type','id','action','sender',
                  'device','source','size','color','shape',
                  'position','direction','question','answer',
                  'other','channel','band','power']
Events.to_csv(katalog_wyniki + "\\Events.txt",index = False) # zapis do pliku
# index = False -> pomijamy indeksy

#****************************************

# "opis_eksperymentu.txt":
czas_rozpoczecia_zapisu_danych_EEG = czas_rozpoczecia_zapisu_danych_EEG_duration.strftime("%H:%M:%S:")
milisekundy = int(czas_rozpoczecia_zapisu_danych_EEG_duration.microsecond / 1000)
# zamiana microsecond => milisekundy
# wtedy możliwe, że będzie coś po przecinku i trzeba to uciąć
czas_rozpoczecia_zapisu_danych_EEG += str(milisekundy)
 
wszystkie_opisy = [nazwa_eksperymentu,
                   uzytkownik_imie_nazwisko,
                   uzytkownik_wiek,
                   uzytkownik_recznosc,
                   uzytkownik_wzrok,
                   uwagi,
                   czas_rozpoczecia_eksperymentu,
                   czas_rozpoczecia_zapisu_danych_EEG,
                   urzadzenie,
                   wzmacniacz,
                   EEG.fs,
                   EEG.ilosc_kanalow,
                   EEG.ilosc_wybranych_kanalow,
                   nazwy_kanalow_urzadzenie,
                   nazwy_kanalow_glowa,
                   EEG.numery_wybranych_kanalow + 1,
                   clock_frequency,
                   typ]
Opis = pd.DataFrame(wszystkie_opisy,dtype = object)
Opis.index = ["nazwa_eksperymentu",
              "uzytkownik_imie_nazwisko",
              "uzytkownik_wiek",
              "uzytkownik_recznosc",
              "uzytkownik_wzrok",
              "uwagi",
              "czas_rozpoczecia_eksperymentu",
              "czas_rozpoczecia_zapisu_danych",
              "urzadzenie",
              "wzmacniacz",
              "fs",
              "ilosc kanalow",
              "ilosc_wybranych_kanalow",
              "nazwy_kanalow_urzadzenie",
              "nazwy_kanalow_glowa",
              "numery_wybranych_kanalow",
              "clock_frequency",
              "typ"]
Opis.to_csv(katalog_wyniki + "\\opis_eksperymentu.txt",header = None,sep=";")
# header = None -> pomijamy nazwy kolumn

#****************************************

# "nazwy_kanalow.txt":
nazwa_txt = katalog_wyniki + "\\Nazwy_kanalow.txt"
fid = open(nazwa_txt,"w")
for kanal in range(len(nazwy_kanalow_glowa)):
    tekst = str(kanal + 1) + " 0 0 0 " + str(nazwy_kanalow_glowa[kanal])
    # np. 1 0 0 0 Fp1
    if kanal != len(nazwy_kanalow_glowa) - 1:
        tekst += "\n"
    fid.write(tekst)
fid.close()

#*****************************************************************************************

print(" =================== KONIEC DZIALANIA PROGRAMU =================== ")

print("Czas trwania programu:",time() - start_programu)
print("Czas trwania eksperymentu:",czas_eksperymentu)
print("Czas zbierania danych EEG:",EEG_czas_caly[-1][0])
print("Probek powinno byc okolo:",int(EEG.fs * EEG_czas_caly[-1][0]))
print("Ilosc faktycznie zebranych probek:",len(EEG_sygnal_caly))

