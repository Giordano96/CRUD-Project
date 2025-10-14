import pandas as pd
import re

# Carica il file CSV
df = pd.read_csv('ricetta_simplified.csv')

# Elenco dei termini non desiderati (88 termini non ingredienti)
non_ingredients = {
    'active', 'aged', 'assorted', 'baked', 'candied', 'canned', 'chilled', 'chopped', 'coarse', 'coarsely',
    'cooked', 'cracked', 'crispy', 'crumbled', 'crushed', 'crystallized', 'cubed', 'diced', 'drained', 'dried',
    'extra', 'extra-virgin', 'finely', 'fresh', 'freshly', 'frozen', 'grated', 'halved', 'herbed', 'high-quality',
    'hulled', 'imitation', 'individually', 'instant', 'large', 'light', 'low', 'low-fat', 'low-sodium', 'mashed',
    'medium', 'melted', 'minced', 'mini', 'miniature', 'mixed', 'optional:', 'peeled', 'pickled', 'prepared',
    'quartered', 'quick', 'quick-cooking', 'raw', 'reduced', 'reduced-fat', 'reduced-sodium', 'refrigerated',
    'roughly', 'seedless', 'sifted', 'sliced', 'soft', 'stemmed', 'stewed', 'sweet', 'sweetened', 'thick', 'thin',
    'thinly', 'toasted', 'unbaked', 'uncooked', 'unflavored', 'unpopped', 'unripe', 'unsweetened', 'very',
    '/4', '1-pint', '100', '13-ounce', '9-inch', 'ball', 'balls', 'box', 'bunch', 'cartons', 'container', 'cube',
    'cubes', 'dash', 'dashes', 'drop', 'drops', 'envelope', 'fluid', 'half', 'head', 'jigger', 'loaf', 'log',
    'piece', 'pint', 'pints', 'pouch', 'pouches', 'scoop', 'sheet', 'sheets', 'slice', 'slices', 'splash', 'sprig',
    'sprigs', 'stalk', 'stalks', 'strips', 'tray', 'triangular', 'wedge', 'wedges', 'and', 'any', 'clear', 'of', 'or'
}


# Funzione per verificare se una riga contiene ingredienti non desiderati
def has_non_ingredient(row):
    if pd.isna(row):  # Gestisci valori nulli
        return False

    # Dividi la stringa in una lista di ingredienti (separati da ' quantity: ')
    ingredients = row.split(' quantity: ')[1:]  # Salta il primo split vuoto
    ingredients = ['quantity: ' + ingredient for ingredient in ingredients]

    # Estrai ogni ingrediente e verifica se è un termine non desiderato
    for entry in ingredients:
        match = re.match(r'quantity: ([\d\.]+) unit: ([^\s]+) ingredient: (.+)', entry)
        if match:
            ingredient = match.groups()[2].lower().strip()  # Estrai l'ingrediente
            # Verifica se l'ingrediente è nei termini non desiderati
            if ingredient in non_ingredients:
                return True
            # Verifica se l'ingrediente contiene un termine non desiderato come parola
            for non_ing in non_ingredients:
                if non_ing in ingredient.split():
                    return True
    return False


# Filtra le righe che non contengono ingredienti non desiderati
df_cleaned = df[~df['ingredients_parsed'].apply(has_non_ingredient)]

# Visualizza il risultato
print(df_cleaned[['recipe_name', 'ingredients_parsed']].head(10))

# Salva il DataFrame pulito in un nuovo file CSV
df_cleaned.to_csv('ricetta_cleaned.csv', index=False)