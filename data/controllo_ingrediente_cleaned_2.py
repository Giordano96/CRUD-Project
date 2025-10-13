import pandas as pd
import re

# Carica il file CSV
df = pd.read_csv('recipes_with_parsed_ingredients.csv')

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
    'sprigs', 'stalk', 'stalks', 'strips', 'tray', 'triangular', 'wedge', 'wedges', 'and', 'any', 'clear', 'of', 'or',
    'bags', 'bing', 'bittersweet', 'black', 'blue', 'bone', 'boneless', 'bosc', 'brown', 'chile-lime', 'chili-lime',
    'clingstone', 'club', 'coconut-flavored', 'colby-monterey', 'conference', 'country-style', 'dijon-style', 'fajita',
    'fruity', 'fuyu', 'hachiya', 'half-and-half', 'heirloom', 'hungarian', 'lemon-lime', 'lemon-pepper', 'long-grain',
    'madras', 'mahi', 'mahlab', 'malt', 'meyer', 'minute', 'naval', 'orange-flavored', 'pear-flavored', 'ranch-style',
    'raspberry-flavored', 'russian-style', 'sazon', 'shaoxing', 'shiraz', 'skin-on', 'southwest', 'strawberry-flavored',
    'taco', 'tangy', 'thai', 'valencia', 'whole-wheat', 'wooden'
}

# Funzione per semplificare un singolo ingrediente (spostata da solo_ingredient.py)
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

# Funzione modificata per pulire e semplificare gli ingredienti parsed (rimuove voci bad, semplifica le buone)
def clean_parsed(row):
    if pd.isna(row):  # Gestisci valori nulli
        return ''

    # Dividi la stringa in una lista di ingredienti (separati da ' quantity: ')
    ingredients = row.split(' quantity: ')[1:]  # Salta il primo split vuoto
    ingredients = ['quantity: ' + ingredient for ingredient in ingredients]

    good = []
    # Estrai ogni ingrediente, verifica se è non desiderato, se no semplifica
    for entry in ingredients:
        match = re.match(r'quantity: ([\d\.]+) unit: ([^\s]+) ingredient: (.+)', entry)
        if match:
            qty = match.group(1)
            unit = match.group(2)
            ing = match.group(3).strip()
            lower_ing = ing.lower()
            # Verifica se l'ingrediente è nei termini non desiderati
            is_bad = lower_ing in non_ingredients
            if not is_bad:
                for non_ing in non_ingredients:
                    if non_ing in lower_ing.split():
                        is_bad = True
                        break
            if is_bad:
                continue  # Salta voci bad
            # Semplifica l'ingrediente buono
            simplified = simplify_ingredient(lower_ing)
            if simplified:  # Aggiungi solo se non è None o vuoto
                good.append(f'quantity: {qty} unit: {unit} ingredient: {simplified}')
    return ' '.join(good)


# Applica la pulizia alle righe
df['ingredients_parsed'] = df['ingredients_parsed'].apply(clean_parsed)

# Elimina le righe dove 'ingredients_parsed' è NaN o vuota (stringa vuota o solo spazi)
df_cleaned = df[df['ingredients_parsed'].notna() & (df['ingredients_parsed'].str.strip() != '')]

# Visualizza il risultato
print(df_cleaned[['recipe_name', 'ingredients_parsed']].head(10))

# Salva il DataFrame pulito in un nuovo file CSV
df_cleaned.to_csv('recipes_with_parsed_ingredients.csv', index=False)