import datetime as d  # obecna data
import numpy as np  # do macierzy
import matplotlib.pyplot as plt
from math import log, sqrt
import scipy.fft as ffft # fft
from sklearn.neighbors import KDTree
from  scipy.spatial.distance import cdist
from scipy.stats import mode
import sklearn.metrics as me
import pylsl as lsl  # przetwarzanie sygnałów z LSL
import os
import shutil # usuniecie folderu wraz z zawartoscia
import pathlib as pt  # tworzenie podfolderów

#*************************************************************************

# input: dwa obiekty klasy datetime
# output: string reprezentujacy roznica czasu miedzy tymi obiektami w sekundach
# latency = ilosc sekund od poczatku eksperymentu

def data_na_latency(sample_time,  czas_rozpoczecia_zapisu_danych_EEG_duration):

    if sample_time  ==  0:
        sample_time = d.datetime.now()  # odpowiednik Matlabowego "clock"
        # c = clock returns a six-element date vector containing the current date and time in decimal form:
        # [year month day hour minute seconds]

    if sample_time > czas_rozpoczecia_zapisu_danych_EEG_duration:
        roznica = sample_time - czas_rozpoczecia_zapisu_danych_EEG_duration # jako rezultat wyjdzie klasa timedelta
        latency = roznica.seconds + roznica.microseconds / 1000000 # zamiana na sekundy
        latency = str(round(latency, 3)) # zaokraglenie do trzech miejsc po przecinku,  zamiana na stringa
    else:
        roznica = czas_rozpoczecia_zapisu_danych_EEG_duration - sample_time
        latency = roznica.seconds + roznica.microseconds / 1000000
        latency = "-" + str(round(latency, 3))

    return(latency)

#*************************************************************************

class Strumien:
    
    def __init__(self, typ, urzadzenie = None, numery_wybranych_kanalow = []):
        self.typ = typ
        # podlaczenie strumienia
        if self.typ  ==  "klawiatura":
            self.inlet = podlacz_klawiature() 
        elif self.typ  ==  "audio":
            self.inlet = podlacz_audio()
        elif self.typ  ==  "EEG":
            self.inlet = podlacz_EEG()
            if urzadzenie  ==  None:
                raise ValueError("Podaj urzadzenie EEG")
            elif urzadzenie != "MITSAR" and urzadzenie != "OPENBCI":
                raise ValueError("Nieprawidlowe urzadzenie EEG (dozwolone: 'MITSAR', 'OPENBCI')")
            else:
                self.urzadzenie = urzadzenie
        else:
            raise ValueError("Niepoprawny typ strumienia (dozwolone:'klawiatura', 'audio', 'EEG')")
        
        if self.typ != "klawiatura":
            # info
            self.inf = self.inlet.info()
            self.extended_inf = self.inf.desc()
            # fs
            self.fs = self.inf.nominal_srate()
            self.ts = 1 / self.fs
            # kanaly
            self.ilosc_kanalow = self.inf.channel_count()
            # nazwy kanalow
            if self.typ  ==  "audio" or urzadzenie  ==  "OPENBCI":
                self.nazwy_kanalow = []
                for channel in range(self.ilosc_kanalow):
                    self.nazwy_kanalow.append("ch" + str(channel + 1))
            elif urzadzenie  ==  "MITSAR":
                self.nazwy_kanalow = []
                ch = self.extended_inf.child("channels").child("channel")
                for k in range(self.ilosc_kanalow):
                    self.nazwy_kanalow.append(ch.child_value("label"))
                    ch = ch.next_sibling()
            self.nazwy_kanalow = np.array(self.nazwy_kanalow)
            if len(numery_wybranych_kanalow) != 0: 
            # zostaly podane wybrane kanaly
                self.numery_wybranych_kanalow = np.array(numery_wybranych_kanalow)
                self.numery_wybranych_kanalow -= 1
                # nazwy wybranych kanalow
                if max(self.numery_wybranych_kanalow) + 1 <= self.ilosc_kanalow:
                # jezeli podane numery kanalow sa prawidlowe
                    self.nazwy_wybranych_kanalow = self.nazwy_kanalow[self.numery_wybranych_kanalow]
                    # dolaczamy tylko te kanaly, ktore okreslono w numery_kanalow
                else:
                    raise ValueError("WYBRANO KANAL O NUMERZE WIEKSZYM NIZ LICZBA KANALOW DOSTARCZONYCH PRZEZ WZMACNIACZ EEG")    
                self.ucinamy = True
                self.ilosc_wybranych_kanalow = len(numery_wybranych_kanalow)
            else: # bierzemy pod uwage wszystkie kanaly
                self.ucinamy = False
                self.ilosc_wybranych_kanalow = self.ilosc_kanalow
                self.numery_wybranych_kanalow = np.arange(self.ilosc_kanalow)
                self.nazwy_wybranych_kanalow = self.nazwy_kanalow
            
    #**********************************************
    
    def pobierz_probke(self, oczekiwanie = False):
        if oczekiwanie  ==  False:
            probka = self.inlet.pull_sample(timeout = 0)[0]
        else:
            if self.typ  ==  "klawiatura":
                print("Wcisnij jakis klawisz")
            probka = self.inlet.pull_sample()[0]
        if probka != None and self.typ != "klawiatura":
            probka = nowa_macierz_2D(probka)
            if self.ucinamy  ==  True:
                probka = probka[:, self.numery_wybranych_kanalow]
        return(probka)
    
    #**********************************************
    
    def pobierz_probki(self, ilosc_sekund = None):
        if self.typ  ==  "klawiatura":
            if ilosc_sekund  ==  None: # nie podano konkretnej ilosci sekund
                # czyli bierzemy wszystko
                probki = self.inlet.pull_chunk()[0]
            else: # podano konkretna ilosc sekund
                print("Wciskaj jakies klawisze")
                probki = self.inlet.pull_chunk(timeout = ilosc_sekund)[0]
        else: # typ to EEG lub audio
            if ilosc_sekund  ==  None:  # nie podano konkretnej ilosci sekund
                # czyli bierzemy wszystko
                ilosc_probek = int(self.fs)
                probki = self.inlet.pull_chunk(max_samples = ilosc_probek)[0]
            else: # podano konkretna ilosc sekund
                ilosc_probek = int(ilosc_sekund * self.fs)
                probki = self.inlet.pull_chunk(max_samples = ilosc_probek, timeout = ilosc_sekund)[0]
                # Pull a chunk of samples from the inlet
                # pull_chunk(self,  timeout = 0.0,  max_samples = 1024,  dest_obj = None)
                # timeout 0.0 czyli tylko próbki dostępne natychmiast
                # raise ValueErrors a tuple (samples, timestamps) where samples is a list of samples
                # (each itself a list of values),  and timestamps is a list of time-stamps.
            if len(probki) != 0:
                    probki = nowa_macierz_2D(probki, pionowa = True)
                    if self.ucinamy  ==  True:
                        probki = probki[:, self.numery_wybranych_kanalow]
                        # z probek wybieramy tylko te kanały,  
                        # które są wskazane w numery_wybranych_kanalow
        return(probki)
    
    #**********************************************
        
    def info(self):
        print("Typ:", self.typ)
        if self.typ != "klawiatura":
            print("Czestotliwosc probkowania:", self.fs)
            print("Ilosc kanalow:", self.ilosc_kanalow)
            print("Nazwy kanalow:", self.nazwy_kanalow)
            print("Ilosc wybranych kanalow:", self.ilosc_wybranych_kanalow)
            print("Numery wybranych kanalow:", self.numery_wybranych_kanalow + 1)
            print("Nazwy wybranych kanalow:", self.nazwy_wybranych_kanalow)
            if self.typ  ==  "EEG":
                print("Urzadzenie:", self.urzadzenie)
        
#*************************************************************************      

def podlacz_klawiature():
    streams_Markers = lsl.resolve_byprop('type', 'Markers', timeout = 2) 
    # czekamy 2 sekundy,  próbując wychwycić strumień typu Markers
    # result jest listą obiektów klasy StreamInfo
    # każdy StreamInfo to jedno wykryte urządzenie (np. myszka,  klawiatura)  
    if len(streams_Markers)  ==  0: 
    # jeśli nie podłączono żadnego urządzenia typu Markers
        raise ValueError("PODLACZ PROGRAM DO OBSLUGI KLAWIATURY (Keyboard.exe)")
    inlet_klawiatura = lsl.StreamInlet(streams_Markers[0])   
    # przypisujemy znaleziony strumień do inletu
    return(inlet_klawiatura)

#*************************************************************************

def podlacz_EEG():
    stream_EEG = lsl.resolve_byprop('type', 'EEG', timeout = 3)  
    if len(stream_EEG)  ==  0: 
    # jeśli nie podłączono żadnego urządzenia typu EEG
        raise ValueError("PODLACZ URZADZENIE DO ODCZYTU SYGNALU EEG")
    inlet_EEG = lsl.StreamInlet(stream_EEG[0]) 
    # przypisujemy znaleziony strumień do inletu
    return(inlet_EEG)

#*************************************************************************

def podlacz_audio():
    stream_Audio = lsl.resolve_byprop('type', 'Audio', timeout = 3)  
    if len(stream_Audio)  ==  0: 
    # jeśli nie podłączono żadnego urządzenia typu EEG
        raise ValueError("PODLACZ URZADZENIE DO ODCZYTU SYGNALU AUDIO")
    inlet_Audio = lsl.StreamInlet(stream_Audio[0]) 
    # przypisujemy znaleziony strumień do inletu
    return(inlet_Audio)

#*************************************************************************

def test_strumienia(klawiatura = False, audio = False, EEG = False):
    
    if audio  ==  True:
        audio1 = Strumien("audio")
        audio1.info()
        print(audio1.pobierz_probke())
        print(audio1.pobierz_probke(oczekiwanie = True))
        print(audio1.pobierz_probki())
        print(audio1.pobierz_probki(ilosc_sekund = 2))
        
        print("\n")
        
        audio2 = Strumien("audio", numery_wybranych_kanalow = [1])
        audio2.info()
        print(audio2.pobierz_probke())
        print(audio2.pobierz_probke(oczekiwanie = True))
        print(audio2.pobierz_probki())
        print(audio2.pobierz_probki(ilosc_sekund = 2))
        
        print("\n")
        
    if EEG  ==  True:
        EEG1 = Strumien("EEG", urzadzenie = "OPENBCI")
        EEG1.info()
        print(EEG1.pobierz_probke())
        print(EEG1.pobierz_probke(oczekiwanie = True))
        print(EEG1.pobierz_probki())
        print(EEG1.pobierz_probki(ilosc_sekund = 2))
        
        print("\n")
        
        EEG2 = Strumien("EEG", numery_wybranych_kanalow = [1], urzadzenie = "OPENBCI")
        EEG2.info()
        print(EEG2.pobierz_probke())
        print(EEG2.pobierz_probke(oczekiwanie = True))
        print(EEG2.pobierz_probki())
        print(EEG2.pobierz_probki(ilosc_sekund = 2))
        
        print("\n")
        
        EEG1 = Strumien("EEG", urzadzenie = "MITSAR")
        EEG1.info()
        print(EEG1.pobierz_probke())
        print(EEG1.pobierz_probke(oczekiwanie = True))
        print(EEG1.pobierz_probki())
        print(EEG1.pobierz_probki(ilosc_sekund = 2))
        
        print("\n")
        
        EEG2 = Strumien("EEG", numery_wybranych_kanalow = [1], urzadzenie = "MITSAR")
        EEG2.info()
        print(EEG2.pobierz_probke())
        print(EEG2.pobierz_probke(oczekiwanie = True))
        print(EEG2.pobierz_probki())
        print(EEG2.pobierz_probki(ilosc_sekund = 2))
    
    if klawiatura  ==  True:                                                                                     
        klawiatura1 = Strumien("klawiatura")
        klawiatura1.info()
        print(klawiatura1.pobierz_probke())
        print(klawiatura1.pobierz_probke(oczekiwanie = True))
        print(klawiatura1.pobierz_probki())
        print(klawiatura1.pobierz_probki(ilosc_sekund = 5))
    
#*************************************************************************

# pionowe = True -> macierz jednowymiarowa zostanie zmieniona w kolumne
# pionowa = False -> macierz jednowymiarowa zostanie zmieniona w wiersz

def nowa_macierz_2D(lista, pionowa = False):
    macierz = np.array(lista)
    if macierz.ndim  ==  1: #jednowymiarowa
        if pionowa  ==  True:
            macierz = np.reshape(macierz, (len(macierz), 1))
        else:
            macierz = np.reshape(macierz, (1, len(macierz)))
    return(macierz)
            
#*************************************************************************

# cel: zapis wektora (macierzy 1D) do pliku

def wektor2plik(nazwa_pliku, macierz, delimiter = " "):
    f = open(nazwa_pliku, "w")
    for i in range(len(macierz)):
        tekst = str(macierz[i])
        if i != len(macierz) - 1:
            tekst += delimiter
        f.write(tekst)
    f.close()
    
#*************************************************************************

def wektor2tekst(macierz, delimiter = " "):
    tekst = ""
    for i in range(len(macierz)):
        tekst += str(macierz[i])
        if i != len(macierz) - 1:
            tekst += delimiter
    return(tekst)
    
#*****************************************************************************

# cel: zapis macierzy 2D do pliku

def macierz2plik(nazwa_pliku, macierz, delimiter = " ", pionowa = True, numeryczna = True):
    if numeryczna  ==  True:
        macierz = nowa_macierz_2D(macierz, pionowa)
    f = open(nazwa_pliku, "w")
    for i in range(len(macierz)):
        tekst_wiersz = ""
        for j in range(len(macierz[i])):
            tekst_wiersz += str(macierz[i][j])
            # chcemy zrobic tak, zeby spacja oddzielala wszystkie dane z wyjatkiem ostatniej w wierszu
            if j != len(macierz[i]) - 1: # jesli nie znajdujemy sie na koncu wiersza
                tekst_wiersz += delimiter
        tekst_wiersz += "\n" # nowa linia pod koniec kazdego wiersza
        f.write(tekst_wiersz) # wpisujemy wiersz do pliku
    f.close()
    
#*****************************************************************************
    
def plik2macierz(nazwa_pliku, delimiter = " ", pionowa = True, dtype = float):
    if dtype  ==  float or dtype  ==  int or dtype  ==  str:
        macierz = np.loadtxt(nazwa_pliku, delimiter = delimiter, ndmin = 1, dtype = dtype)
    else:
        raise ValueError("Niepoprawny typ danych. Dozwolone: int,  float,  str")
    macierz = nowa_macierz_2D(macierz, pionowa)
    return(macierz)

#*****************************************************************************

def plik2wartosc(nazwa_pliku, dtype = float):
    f = open(nazwa_pliku)
    wartosc = f.read()
    f.close()
    wartosc = dtype(wartosc)
    return(wartosc)

#**************************************************************************

def napis2macierz(napis, typ = None):
    if napis[0] != "[":
        raise ValueError("Podany napis nie jest macierza")
    if napis[2]  ==  "[":
        raise ValueError("Macierz ma zbyt duzo wymiarow")
    if napis[1] != "[": # macierz jednowymiarowa
        macierz = napis.replace("[", "").replace("]", "").replace(", ", "")
        macierz = macierz.split()
    else: # macierz dwuwymiarowa
        if napis.count(", ")  ==  0: # numpy
            temp = napis.split("]\n")
        else: # lista
            temp = napis.split("], ")
        macierz = []
        for row in temp:
            wiersz = row.replace("[", "").replace("]", "").replace(", ", "")
            wiersz = wiersz.split()
            macierz.append(wiersz)
    if typ  ==  None or typ  ==  float:
        macierz = np.array(macierz, dtype = float)
    elif typ  ==  int:
        macierz = np.array(macierz, dtype = int)
    return(macierz)

#**************************************************************************

def slownik2plik(nazwa_pliku, slownik, delimiter = ", "):
    napis = ""
    klucze = list(slownik.keys())
    wartosci = list(slownik.values())
    for i in range(len(slownik)):
        napis += "{0}{1}{2}".format(klucze[i], delimiter, wartosci[i])
        if i != len(slownik) - 1:
            napis += "\n"
    f = open(nazwa_pliku, "w")
    f.write(napis)
    f.close()

#**************************************************************************

# zakladamy, ze klucz jest typu int, a wartosc typu string

def plik2slownik(nazwa_pliku, delimiter = ", "):
    macierz = plik2macierz(nazwa_pliku, delimiter = delimiter, pionowa = False, dtype = str)
    slownik = {}
    for i in range(len(macierz)):
        slownik[int(macierz[i][0])] = str(macierz[i][1])
    return(slownik)

#**************************************************************************

def kategorie2slownik(kategorie, kategorie_numeric):
    kategorie = nowa_macierz_2D(kategorie, pionowa = True)
    kategorie_numeric = nowa_macierz_2D(kategorie_numeric, pionowa = True)
    slownik_kategorie = {}
    for i in range(len(kategorie)):
        slownik_kategorie[kategorie_numeric[i][0]] = str(kategorie[i][0])
    return(slownik_kategorie)
    
#**************************************************************************

# jeżeli katalog istnieje,  chcemy go usunac,  a potem stworzyc od nowa

def stworz_folder(katalog):
    if os.path.isdir(katalog) == 1:  
        shutil.rmtree(katalog)
    pt.Path(katalog).mkdir(parents=True,  exist_ok=True)   
    
#**************************************************************************
    
def usun_plik(plik):
    if os.path.isfile(plik) == 1:
        os.remove(plik) 

#**************************************************************************

def live_plotter(x_vec, y1_data, line1, slownik, najwyzszy_stan, pause_time = 0.1):
    if line1 == []:
        # this is the call to matplotlib that allows dynamic plotting
        plt.ion()
        fig = plt.figure(figsize = (8, 6))
        ax = fig.add_subplot(111)
        # create a variable for the line so we can later update it
        line1,  = ax.plot(x_vec, y1_data)   
        for i in range(len(slownik)): 
            pozycja_x = -1
            pozycja_y = int(slownik[i][0]) - 0.04
            tekst = slownik[i][1]
            plt.text(pozycja_x, pozycja_y, tekst) # nie dziala :(
        plt.title(str(slownik))
        plt.ylim(-0.1, najwyzszy_stan + 0.1)
        plt.show()
    
    # after the figure,  axis,  and line are created,  we only need to update the y-data
    line1.set_ydata(y1_data)
    # this pauses the data so the figure/axis can catch up - the amount of pause can be altered above
    plt.pause(pause_time)
    
    # return line so we can update it again in the next iteration
    return line1

#**************************************************************************

def fft(x):
    X = ffft.fft(x)
    return(X.real, X.imag)

#*****************************************************************************

def moduly(X_real, X_im, fs, decybele = 1):

    N = int(len(X_real) / 2 - 1)
    A = []
    f = [] # czestotliwosc prazkow widma
    stala = fs/len(X_real)
    
    if decybele  ==  1: # A w decybelach - A[dB] 
        
        for i in range(N):
            f.append(i * stala)
            A.append(10 * log(sqrt(X_real[i]**2 + X_im[i]**2),  10)) 
    
    else: # A w zwykłych jednostkach
        
        for i in range(N):
            f.append(i * stala)
            A.append(sqrt(X_real[i]**2 + X_im[i]**2))

    return(f, A) 

#*****************************************************************************

# zamiana x(t) na A(f)
# x(t) musi byc wektorem!
def widmo_amplitudowe(x, fs, use_dB = 0):
    X_real, X_im = fft(x)
    f, A = moduly(X_real, X_im, fs, use_dB)
    return(f, A)

#*****************************************************************************

def wykres_EEG(czas, sygnal):
    x = czas
    y = sygnal
    
    for i in range(y.shape[1]):
        plt.figure()
        plt.plot(x, y[:, i])
        plt.title("Kanal " + str(i + 1))
        plt.xlabel("czas t [s]")
        plt.ylabel("Sygnal EEG")

#*****************************************************************************

class KNN:
# KNN - k nearest neighbours
    
    def __init__(self, liczba_sasiadow = 1, use_KDtree = False):
        self.k = liczba_sasiadow
        self.use_KDtree = use_KDtree
        
    #***************************************************************************
       
    def fit(self, punkty, kategorie):
        punkty = nowa_macierz_2D(punkty)
        kategorie = nowa_macierz_2D(kategorie, pionowa = True)
        if len(punkty) != len(kategorie):
            raise ValueError("Punkty nie pasuja do kategorii. Len(punkty) = ", len(punkty), "len(kategorie) = ", len(kategorie))
        if self.use_KDtree  ==  True:
            self.tree = KDTree(punkty)
        else:
            self.punkty = punkty
        self.kategorie = np.array(kategorie)

    #***************************************************************************
            
    def predict(self, nowe_punkty, use_regression = False):
        nowe_punkty = nowa_macierz_2D(nowe_punkty)
        if self.use_KDtree  ==  False:
            odleglosci = cdist(nowe_punkty,  self.punkty,  'euclidean')
            # ilosc wierszy = ilosc nowych punktów
            # ilosc kolumny = ilosc starych punktów
            # w srodku odleglosci miedzy punktami
            indeksy_najblizszych_punktow = np.argpartition(odleglosci, self.k)[:, 0:self.k]
            # indeksy k najmniejszych elementow z kazdego wiersza
            # dla kazdego nowego punktu szukamy k najblizszych ze starych punktow
        else:
            indeksy_najblizszych_punktow = self.tree.query(nowe_punkty, k = self.k)[1]
        kategorie_najblizszych_punktow = self.kategorie[indeksy_najblizszych_punktow]
        if use_regression  ==  False:
            # wybieramy najczestsza kategorie w danym wierszu
            # sa to wyznaczone kategorie dla nowe_punkty
            najczestsza_kategoria = mode(kategorie_najblizszych_punktow, axis = 1)[0]
            return(najczestsza_kategoria)
        else:
            return(np.mean(kategorie_najblizszych_punktow, axis = 1))
        
    #***************************************************************************
    
    def score(self, pseudo_nowe_punkty, prawdziwe_kategorie_nowych_punktow, use_regression = False):
        pseudo_nowe_punkty = nowa_macierz_2D(pseudo_nowe_punkty)
        przewidywane_kategorie_nowych_punktow = self.predict(pseudo_nowe_punkty, use_regression)
        if use_regression  ==  False:
            # sprawdzamy dokladnosc naszych przewidywan
            wektor_logiczny = (prawdziwe_kategorie_nowych_punktow  ==  przewidywane_kategorie_nowych_punktow[:, 0])
            suma = np.sum(wektor_logiczny)
            procentowa_dokladnosc = suma / len(prawdziwe_kategorie_nowych_punktow) * 100
            return(procentowa_dokladnosc)
        else: 
            blad_sredniokwadratowy = me.mean_squared_error(prawdziwe_kategorie_nowych_punktow, przewidywane_kategorie_nowych_punktow)
            return(blad_sredniokwadratowy)
        
#****************************************************************************
    
def analiza_dokladnosci(punkty, kategorie, k):
    suma_dokladnosci = 0
    for i in range(len(punkty)):
        pseudo_nowy_punkt = list([punkty[i]])
        pseudo_stare_punkty = list(punkty.copy())
        del pseudo_stare_punkty[i] #usuwamy jeden punkt
        kategoria_pseudo_nowego_punkty = [kategorie[i]] #jednoelementowa lista
        kategorie_pseudo_stare_punkty = list(kategorie.copy())
        del kategorie_pseudo_stare_punkty[i]
        knn = KNN(k)
        knn.fit(pseudo_stare_punkty, kategorie_pseudo_stare_punkty)
        dokladnosc = knn.score(pseudo_nowy_punkt, kategoria_pseudo_nowego_punkty)
        suma_dokladnosci += dokladnosc
    srednia_dokladnosc = suma_dokladnosci / len(punkty)
    return(round(srednia_dokladnosc, 2))


    