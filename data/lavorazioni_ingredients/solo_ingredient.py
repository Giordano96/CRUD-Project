import pandas as pd
import re

# Carica il file CSV
df = pd.read_csv('ricetta.csv')


# Funzione per semplificare un singolo ingrediente
def simplify_ingredient(ingredient):
    if pd.isna(ingredient):  # Gestisci valori nulli
        return None

    ingredient = ingredient.lower().strip()  # Converti in minuscolo e rimuovi spazi

    # Dizionario per standardizzare ingredienti simili
    ingredient_map = {
        'apple': ['apple', 'granny smith', 'macintosh', 'tart apples'],
        'pastry': ['puff pastry', 'pie pastry', 'double-crust pie', 'pie shell'],
        'sugar': ['white sugar', 'brown sugar', 'confectioners sugar', 'packed brown sugar'],
        'butter': ['butter', 'unsalted butter', 'cold butter'],
        'flour': ['all-purpose flour', 'sifted all-purpose flour', 'blanched almond flour'],
        'cinnamon': ['ground cinnamon', 'cinnamon sticks'],
        'date': ['dates', 'medjool dates', 'pitted dates', 'chopped dates'],
        'nut': ['walnuts', 'pecans', 'almonds', 'cashews', 'chopped walnuts', 'chopped pecans'],
        'oats': ['rolled oats', 'quick-cooking oats', 'old-fashioned oats'],
        'raisin': ['raisins', 'golden raisins'],
        'water': ['water', 'boiling water', 'cold water'],
        'coconut': ['shredded coconut', 'flaked coconut', 'unsweetened shredded coconut'],
        'milk': ['milk', 'heavy cream', 'sweetened condensed milk'],
        'oil': ['vegetable oil', 'coconut oil', 'sesame oil', 'grapeseed oil'],
        'salt': ['salt', 'kosher salt', 'sea salt'],
        'vanilla': ['vanilla extract', 'vanilla'],
        'cherry': ['mar cherry', 'cherries', 'dried cherries'],
        'lemon juice': ['lemon juice', 'squeeze lemon juice'],
        'allspice': ['whole allspice', 'allspice'],
        'cloves': ['whole cloves', 'cloves'],
    }

    # Cerca il nome base dell'ingrediente
    for base_name, variants in ingredient_map.items():
        for variant in variants:
            if variant in ingredient:
                return base_name

    # Se non trovato, rimuovi aggettivi e dettagli (es. "chopped", "peeled")
    words = ingredient.split()
    for word in words:
        if word not in ['chopped', 'peeled', 'cored', 'sliced', 'diced', 'pitted', 'fresh', 'finely', 'packed',
                        'ground', 'whole', 'large', 'small', 'roasted', 'unsalted']:
            return word

    # Se non si riesce a semplificare, restituisci l'ingrediente originale
    return ingredient


# Funzione per estrarre e semplificare gli ingredienti da una riga
def extract_ingredients(row):
    if pd.isna(row):  # Gestisci valori nulli
        return []

    # Dividi la stringa in una lista di ingredienti (separati da ' quantity: ')
    ingredients = row.split(' quantity: ')[1:]  # Salta il primo split vuoto
    ingredients = ['quantity: ' + ingredient for ingredient in ingredients]

    # Estrai e semplifica ogni ingrediente
    simplified_ingredients = []
    for entry in ingredients:
        match = re.match(r'quantity: ([\d\.]+) unit: ([^\s]+) ingredient: (.+)', entry)
        if match:
            ingredient = match.groups()[2]  # Estrai solo l'ingrediente
            simplified = simplify_ingredient(ingredient)
            if simplified:  # Aggiungi solo se non è None
                simplified_ingredients.append(simplified)

    return simplified_ingredients


# Raccogli tutti gli ingredienti unici
unique_ingredients = set()
for row in df['ingredients_parsed']:
    ingredients = extract_ingredients(row)
    unique_ingredients.update(ingredients)

# Converti l'insieme in una lista ordinata
unique_ingredients = sorted(unique_ingredients)

# Scrivi la lista nel file di testo
with open('lista_ingredienti.txt', 'w', encoding='utf-8') as f:
    f.write('lista ingredienti: ' + ', '.join(unique_ingredients))

# Stampa la lista per verifica
print('lista ingredienti: ' + ', '.join(unique_ingredients))