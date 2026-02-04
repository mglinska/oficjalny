# program, ktory wysyla w pliku "stymulacja.txt" obecna epoke (stan)

#*************************************************************************

import os
import time
import datetime as d
import funkcje_orly as fnk

#*************************************************************************

# parametry do ustawienia

# typ = "EEG"
typ = "audio"

ilosc_stanow = 3
dlugosc_trwania_zdarzenia = 6
ilosc_cykli = 3

# epoki:
if typ == "EEG":
    if ilosc_stanow == 2:
        actions = ["relaks", "mruganie"] * ilosc_cykli
    elif ilosc_stanow == 3:
        actions = ["relaks", "mowienie", "mruganie"] * ilosc_cykli
else:
    if ilosc_stanow == 2:
        actions = ["cisza", "mowienie"] * ilosc_cykli
    elif ilosc_stanow == 3:
        actions = ["cisza", "mowienie", "gwizdanie"] * ilosc_cykli
actions.append("end scenario")

#*************************************************************************

sciezka = "stymulacja.txt"
koniec = 0

for i in range(len(actions)): # ilosc zdarzen

    # ponizsze kombinowanie wynika wylacznie z tego, ze wydarzenia maja 
    # miec milisekundy a nie microsekundy
    dzien = d.datetime.now()
    czas_zdarzenia = dzien.strftime("%Y%m%d%H%M%S") # brakuje milisekund
    mili = str(int(dzien.microsecond / 1000))
    # dodanie zer, zeby byla zawsze taka sama ilosc cyfr (003,013,125):
    if len(mili) == 1:
        mili = "00" + mili
    elif len(mili) == 2:
        mili = "0" + mili
    czas_zdarzenia = czas_zdarzenia + mili
    
    zacznij_oczekiwanie = time.time()

    while koniec == 0: # z dodaniem nowego zdarzenia czekamy tak dlugo az...
            
        if os.path.isfile(sciezka) == 0: 
        # ... plik "stymulacja.txt" nie bedzie istnial
        # wtedy bedzie mozna stworzyc go na nowo i wpisac jedno nowe zdarzenie
        # plik "stymulacja.txt" jest usuwany w programie glownym, gdy tylko
        # program glowny odczyta zdarzenie i doda je do wektora zdarzen
        # innymi slowy tak naprawde "while koniec == 0" czeka, az uruchomimy program glowny
            
            action = actions[i]
            print(action)
            # zapis nowego zdarzenia do pliku "stymulacja.txt"
            event = [czas_zdarzenia,'flag','none', action,'Keyboard','Keyboard','Matlab','none','none','none','none','none','none','none','none','none','none','none']
            # [czas_zdarzenia,type,id,action,sender,device,source,size,color,shape,position,direction,question,answer,other,channel,band,power]
            fnk.wektor2plik(sciezka,event,delimiter=",")

            time.sleep(dlugosc_trwania_zdarzenia) # czekamy z kolejnym zdarzeniem
            
            break
        
        if time.time() - zacznij_oczekiwanie > 5:
            print("ZBYT DLUGI CZAS OCZEKIWANIA NA PROGRAM GLOWNY")
            koniec = 1
            
    if koniec == 1:
        break
        
time.sleep(3)
fnk.usun_plik(sciezka)

        

