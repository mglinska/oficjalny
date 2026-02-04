# program, ktory pokazuje na wykresie biezacy stan
# stan odczytywany jest z folderu "analiza"

#*************************************************************************

import numpy as np
import os
import funkcje_orly as fnk
from funkcje_orly import Strumien

#*************************************************************************

# parametry do ustawienia

katalog = r"C:\Users\sheil\Desktop\S4\ORŁY\PROJEKT_GŁÓWNY\output_data\Rejestracja_test\NIKT\kanal1,3stany"

#*************************************************************************

# podlaczenie klawiatury

klawiatura = Strumien("klawiatura")

#*************************************************************************

# odczyt danych z plikow

plik = katalog + "\\analiza\\obecny_stan.txt"
if os.path.isfile(plik) == 0:
    print("Oczekiwanie na rozpoczecie eksperymentu")
while os.path.isfile(plik) == 0:
    pass
stan = fnk.plik2wartosc(plik,dtype=int)

slownik = np.loadtxt(katalog + "\\analiza\\slownik_kategorii.txt",dtype=object,delimiter=",")
najwyzszy_stan = max(np.array(slownik[:,0],dtype=int))

#*************************************************************************

size = 100
x_vec = np.linspace(0,1,size+1)[0:-1] 
y_vec = np.array([stan] * len(x_vec))
line1 = []

#*************************************************************************

klawisz = None

while klawisz == None or str(klawisz[0]) != "ESCAPE pressed":

    stan = fnk.plik2wartosc(plik,dtype=int)
    y_vec[-1] = stan # co iteracje zmieniamy ostatni punkt wektora, a konkretnie jego wspolrzedna y
    line1 = fnk.live_plotter(x_vec,y_vec,line1,slownik,najwyzszy_stan)
    y_vec = np.append(y_vec[1:],0.0) # wyrzucamy pierwszy punkt, dodajemy punkt o y = 0

    klawisz = klawiatura.pobierz_probke()
  
 
# "legenda", ktora dziala w Spyderze:
    
# slownik = [["0","cisza"],
#            ["2","mowienie"],
#            ["1","gwizdanie"]]
# slownik = np.array(slownik)
# najwyzszy_stan = max(np.array(slownik[:,0],dtype=int))

# x = [0,1,2,3]
# y = [0,1,2,1]

# plt.plot(x,y)
# for i in range(len(slownik)):
#     pozycja_x = -1
#     pozycja_y = int(slownik[i][0]) - 0.04
#     tekst = slownik[i][1]
#     plt.text(pozycja_x,pozycja_y,tekst)
# plt.ylim(-0.1,najwyzszy_stan + 0.1)


    

