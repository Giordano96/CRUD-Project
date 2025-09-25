from fractions import Fraction
import pandas as pd
import re

# ---------- Caricamento dataset ----------
df = pd.read_csv("recipes.csv")
df = df.dropna().drop_duplicates()

# ---------- Pulizia testo ----------
colonne_testo = ["recipe_name", "ingredients", "cuisine_path", "nutrition"]
for col in colonne_testo:
    df[col] = df[col].str.lower().str.strip()

# ---------- Definizioni ----------
UNITA = [
    "cup", "cups", "tablespoon", "tablespoons", "tbsp", "teaspoon", "teaspoons",
    "tsp", "g", "kg", "ml", "l", "oz", "pound", "pounds"
]

FRAZIONI_UNICODE = {
    "½": 0.5, "¼": 0.25, "¾": 0.75, "⅓": 1/3, "⅔": 2/3, "⅛": 0.125
}

CONVERSIONE_UNIT = {
    "tsp": ("ml", 5),
    "teaspoon": ("ml", 5),
    "teaspoons": ("ml", 5),
    "tbsp": ("ml", 15),
    "tablespoon": ("ml", 15),
    "tablespoons": ("ml", 15),
    "cup": ("ml", 240),
    "cups": ("ml", 240),
    "oz": ("g", 28.35),
    "pound": ("g", 454),
    "pounds": ("g", 454)
}

DENSITA_INGREDIENTE = {
    "sugar": 0.85,
    "white sugar": 0.85,
    "brown sugar": 0.95,
    "butter": 0.95,
    "flour": 0.53,
    "all-purpose flour": 0.53,
    "water": 1.0,
    "milk": 1.03,
    "vegetable oil": 0.92,
    "apples": 0.6,
    "oats": 0.38
}

PAROLE_PREPARAZIONE = [
    "peeled", "cored", "sliced", "diced", "mashed", "minced", "grated",
    "chopped", "melted", "packed", "quick-cooking", "ground", "beaten"
]

MAPPING_INGREDIENTI = {
    "all-purpose flour": "flour",
    "white sugar": "sugar",
    "brown sugar": "sugar",
    "quick-cooking oats": "oats",
    "ground cinnamon": "cinnamon",
    "all-purpose apples": "apples",
    "butter": "butter",
    "water": "water",
    "milk": "milk",
    "vegetable oil": "vegetable oil",
    "baking powder": "baking powder",
    "baking soda": "baking soda"
}

# ---------- Funzioni ----------

def converti_quantita(q_str):
    q_str = q_str.strip()
    if q_str in FRAZIONI_UNICODE:
        return FRAZIONI_UNICODE[q_str]
    try:
        return float(Fraction(q_str))
    except:
        return None

def converti_unit_metrico(quantita, unita):
    if quantita is None or unita is None:
        return quantita, unita
    unita = unita.lower()
    if unita in CONVERSIONE_UNIT:
        nuova_unita, fattore = CONVERSIONE_UNIT[unita]
        return quantita * fattore, nuova_unita
    return quantita, unita

def converti_volume_in_peso(quantita, unita, ingrediente):
    if quantita is None or unita is None or ingrediente is None:
        return quantita, unita
    ingrediente = ingrediente.lower()
    if unita == "ml" and ingrediente in DENSITA_INGREDIENTE:
        g_per_ml = DENSITA_INGREDIENTE[ingrediente]
        return quantita * g_per_ml, "g"
    return quantita, unita

def pulisci_descrizione(nome):
    return re.sub(r"[-,].*$", "", nome).strip()

def estrai_quantita_unita(nome):
    pattern = r"^([\d½¼¾⅓⅔⅛\/\.]+)\s*(cup|cups|tablespoon|tablespoons|tbsp|teaspoon|teaspoons|tsp|g|kg|ml|l|oz|pound|pounds)?\s*(.*)$"
    match = re.match(pattern, nome)
    if match:
        quantita_str, unita, ingrediente = match.groups()
        quantita = converti_quantita(quantita_str)
        ingrediente = pulisci_descrizione(ingrediente)
        if unita is None:
            for u in ["cup", "cups", "tablespoon", "tablespoons", "tbsp",
                      "teaspoon", "teaspoons", "tsp"]:
                if ingrediente.startswith(u):
                    unita = u
                    ingrediente = ingrediente[len(u):].strip()
                    break
        return quantita, unita, ingrediente.strip()
    return None, None, nome

def separa_ingredienti(stringa):
    risultato = []
    if not isinstance(stringa, str):
        return risultato
    lista_ingredienti = stringa.split(",")
    for ingr in lista_ingredienti:
        ingr = ingr.strip().lower()
        quantita, unita, nome = estrai_quantita_unita(ingr)
        risultato.append({
            "quantita": quantita,
            "unita": unita,
            "ingrediente": nome
        })
    return risultato

def pulisci_e_normalizza_ingredienti(lista_ingredienti):
    nuova_lista = []
    for ingr in lista_ingredienti:
        nome = ingr["ingrediente"]
        if nome in PAROLE_PREPARAZIONE or nome == "":
            continue
        if nome in MAPPING_INGREDIENTI:
            ingr["ingrediente"] = MAPPING_INGREDIENTI[nome]
        nuova_lista.append(ingr)
    return nuova_lista

def converti_ingredients_metric(lista_ingredienti):
    nuova_lista = []
    for ingr in lista_ingredienti:
        q, u = converti_unit_metrico(ingr["quantita"], ingr["unita"])
        ingr["quantita"], ingr["unita"] = converti_volume_in_peso(q, u, ingr["ingrediente"])
        nuova_lista.append(ingr)
    return nuova_lista

def unisci_ingredienti(lista_ingredienti):
    aggregati = {}
    for ingr in lista_ingredienti:
        nome = ingr["ingrediente"]
        unita = ingr["unita"]
        quantita = ingr["quantita"] or 0
        if nome in aggregati:
            if aggregati[nome]["unita"] == unita:
                aggregati[nome]["quantita"] += quantita
            else:
                nome_chiave = f"{nome} ({unita})"
                if nome_chiave in aggregati:
                    aggregati[nome_chiave]["quantita"] += quantita
                else:
                    aggregati[nome_chiave] = {"ingrediente": nome, "quantita": quantita, "unita": unita}
        else:
            aggregati[nome] = {"ingrediente": nome, "quantita": quantita, "unita": unita}
    return list(aggregati.values())

# ---------- Applicazione finale ----------
df["ingredients_puliti"] = df["ingredients"].apply(separa_ingredienti)
df["ingredients_puliti"] = df["ingredients_puliti"].apply(converti_ingredients_metric)
df["ingredients_puliti"] = df["ingredients_puliti"].apply(pulisci_e_normalizza_ingredienti)
df["ingredients_puliti"] = df["ingredients_puliti"].apply(unisci_ingredienti)

# ---------- Salvataggio ----------
df.to_csv("recipes_clean.csv", index=False)
print("File recipes_clean.csv salvato con successo!")
