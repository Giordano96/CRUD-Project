import pandas as pd
import re
from collections import Counter

# Mappatura statica degli ingredienti base, estesa con più ingredienti comuni
base_ingredients_map = {
    'butter': 'butter',
    'unsalted butter': 'butter',
    'salted butter': 'butter',
    'apples': 'apple',
    'granny smith apples': 'apple',
    'macintosh apples': 'apple',
    'sugar': 'sugar',
    'white sugar': 'sugar',
    'brown sugar': 'sugar',
    'granulated sugar': 'sugar',
    'powdered sugar': 'sugar',
    'confectioners\' sugar': 'sugar',
    'cranberries': 'cranberries',
    'fresh cranberries': 'cranberries',
    'dried cranberries': 'cranberries',
    'pastry': 'pastry',
    'pie crust': 'pastry',
    'puff pastry': 'pastry',
    'frozen puff pastry': 'pastry',
    'doublecrust pie pastry': 'pastry',
    'egg': 'egg',
    'eggs': 'egg',
    'egg white': 'egg',
    'cream': 'cream',
    'heavy cream': 'cream',
    'whipping cream': 'cream',
    'whipped cream': 'cream',
    'ice cream': 'cream',
    'flour': 'flour',
    'all-purpose flour': 'flour',
    'whole wheat flour': 'flour',
    'water': 'water',
    'cinnamon': 'cinnamon',
    'ground cinnamon': 'cinnamon',
    'salt': 'salt',
    'kosher salt': 'salt',
    'oil': 'oil',
    'olive oil': 'oil',
    'vegetable oil': 'oil',
    'milk': 'milk',
    'whole milk': 'milk',
    'chicken': 'chicken',
    'chicken breast': 'chicken',
    'chicken thighs': 'chicken',
    'cheese': 'cheese',
    'cheddar cheese': 'cheese',
    'parmesan cheese': 'cheese',
    'vanilla': 'vanilla',
    'vanilla extract': 'vanilla',
    'lemon': 'lemon',
    'lemon juice': 'lemon',
    'onion': 'onion',
    'garlic': 'garlic',
    'pepper': 'pepper',
    'black pepper': 'pepper',
    'bell pepper': 'pepper',
    'tomato': 'tomato',
    'tomatoes': 'tomato',
    'potato': 'potato',
    'potatoes': 'potato',
    'carrot': 'carrot',
    'carrots': 'carrot',
    'honey': 'honey',
    'vinegar': 'vinegar',
    'balsamic vinegar': 'vinegar',
    'baking powder': 'baking powder',
    'baking soda': 'baking soda',
    'chili powder': 'chili powder',
    'prunes': 'prune',
    'shrimp': 'shrimp',
    'medium shrimp': 'shrimp',
    'spinach': 'spinach',
    'frozen chopped spinach': 'spinach',
    'red lentils': 'lentil',
    'chuck roast': 'beef',
    'papaya': 'papaya',
    'peach': 'peach',
    'fresh peach': 'peach',
    'thyme': 'thyme',
    'fresh thyme': 'thyme',
    'fresh thyme leaves': 'thyme',
    'cumin': 'cumin',
    'basil': 'basil',
    'basil leaves': 'basil',
    'fruit cocktail': 'fruit cocktail',
    'gin': 'gin',
    'apricot': 'apricot',
    'fresh apricot': 'apricot',
    'bread crumbs': 'bread crumbs',
    'seasoned bread crumbs': 'bread crumbs',
    'jello': 'jello',
    'cherry jell-o': 'jello',
    'jicama': 'jicama',
    'cabbage': 'cabbage',
    'cherries': 'cherry',
    'fresh cherries': 'cherry',
    'mozzarella': 'mozzarella',
    'fresh mozzarella': 'mozzarella',
    'pork ribs': 'pork',
    'countrystyle pork ribs': 'pork',
    'figs': 'fig',
    'canned figs': 'fig',
    'prickly pears': 'prickly pear',
    'harissa': 'harissa',
    'berries': 'berry',
    'seasonal berries': 'berry',
    'grapes': 'grape',
    'green grapes': 'grape',
    'tomatillos': 'tomatillo',
    'fresh tomatillos': 'tomatillo',
    'oats': 'oats',
    'quick cooking oats': 'oats',
    'chocolate chips': 'chocolate chip',
    'mango': 'mango',
    'minced mango': 'mango',
    'almonds': 'almond',
    'celery': 'celery',
    'corn syrup': 'corn syrup',
    'light corn syrup': 'corn syrup',
    'clove': 'clove',
    'whole cloves': 'clove',
    'allspice': 'allspice',
    'whole allspice': 'allspice',
    'maple syrup': 'maple syrup',
    'coconut': 'coconut',
    'flaked coconut': 'coconut',
    'shredded coconut': 'coconut',
    'unsweetened shredded coconut': 'coconut',
    'beef bouillon': 'beef bouillon',
    'beef bouillon powder': 'beef bouillon',
    'cocoa powder': 'cocoa powder',
    'unsweetened cocoa powder': 'cocoa powder',
    'cayenne pepper': 'cayenne pepper',
    'chicken broth': 'chicken broth',
    'rice': 'rice',
    'white rice': 'rice',
    'walnut': 'walnut',
    'walnuts': 'walnut',
    'chopped walnuts': 'walnut',
    'corn flakes': 'corn flakes',
    'corn flakes cereal': 'corn flakes',
    'crushed corn flakes': 'corn flakes',
    'rice cereal': 'rice cereal',
    'crispy rice cereal': 'rice cereal',
    'banana': 'banana',
    'bananas': 'banana',
    'pineapple': 'pineapple',
    'crushed pineapple': 'pineapple',
    'almond flour': 'almond flour',
    'blanched almond flour': 'almond flour',
    'chia seeds': 'chia seeds',
    'cashew': 'cashew',
    'cashews': 'cashew',
    'raw cashews': 'cashew',
    'espresso': 'espresso',
    'cold espresso': 'espresso',
    'cornstarch': 'cornstarch',
    'nutmeg': 'nutmeg',
    'ground nutmeg': 'nutmeg',
    'pecan': 'pecan',
    'pecans': 'pecan',
    'pecan halves': 'pecan',
    'raisin': 'raisin',
    'raisins': 'raisin',
    'golden raisins': 'raisin',
    'golden raisin': 'raisin',
    'date': 'date',
    'dates': 'date',
    'chopped dates': 'date',
    'pitted dates': 'date',
    'curry': 'curry',
    'curry powder': 'curry',
    'apple cider': 'apple cider',
    'bottle apple cider': 'apple cider',
    'noodles': 'noodles',
    'roasted noodles': 'noodles',
    'grapeseed oil': 'oil',
    'sea salt': 'salt',
    'medjool dates': 'date',
    'basmati rice': 'rice',
    'saffron threads': 'saffron',
    'boiling water': 'water',
    'boiled water': 'water',
}

# Lista di parole da rimuovere (estesa per eliminare non-ingredienti)
removal_words = [
    'cored', 'sliced', 'peeled', 'chopped', 'diced', 'minced', 'grated', 'beaten', 'whipped', 'fresh', 'dried', 'packed',
    'medium', 'small', 'large', 'ground', 'crushed', 'pureed', 'halved', 'quartered', 'finely', 'coarsely', 'melted',
    'softened', 'room temperature', 'to taste', 'for dusting', 'and', 'or', 'as needed', 'lightly', 'thawed', 'optional',
    'other', 'firm', 'soft-textured', 'that fall apart when cooked', 'frozen', 'sheet', 'package', 'still cold', 'white',
    'sweetened', 'into', 'rings', 'inch', 'thick', 'country style', 'seasoned', 'but', 'ice', 'allpurpose', 'quick cooking',
    'crisp', 'doublecrust', 'cup', 'tablespoon', 'teaspoon', 'ounce', 'oz', 'pound', 'lb', 'divided', 'heated', 'skinless',
    'boneless', 'rinsed', 'drained', 'whole', 'sticks', 'stalks', 'bottle', 'can', 'roasted', 'unsalted', 'salted', 'cold',
    'hot', 'boiling', 'roughly', 'pitted', 'raw', 'boiled', 'cubed', 'more', 'handles', 'quarter', 'rounds', 'wooden',
    'chopsticks', 'for', 'with', 'from', 'in', 'the', 'a', 'an', 'of', 'at', 'by', 'on'
]

# Pattern per rimuovere quantità, unità, descrizioni, etc.
removal_pattern = r'\b(?:' + '|'.join(removal_words) + r')\b|[½¼¾⅓⅔⅛⅜⅝⅞]|\d+/\d+|\d*\.?\d*\s*(?:tablespoons?|teaspoons?|cups?|ounces?|oz|pounds?|lb|inches?|inch)?|\([^)]*\)|-|:|;|,'


def generate_dynamic_mapping(all_ingredients, min_freq=1):
    """Genera una mappatura dinamica per ingredienti frequenti."""
    dynamic_map = {}
    ingredient_counts = Counter(all_ingredients)
    common_ingredients = [ing for ing, count in ingredient_counts.items() if count >= min_freq]

    for ingredient in common_ingredients:
        # Pulisci l'ingrediente senza introdurre spazi tra le lettere
        cleaned = re.sub(removal_pattern, '', ingredient, flags=re.IGNORECASE).strip()
        # Normalizza spazi multipli
        cleaned = ' '.join(cleaned.split()).strip()
        if cleaned and cleaned not in base_ingredients_map.values() and cleaned not in dynamic_map.values():
            # Correggi termini concatenati (esteso con più correzioni)
            cleaned = (cleaned.replace('brownsugar', 'brown sugar')
                       .replace('grannysmithapples', 'apple')
                       .replace('macintoshapples', 'apple')
                       .replace('puffpastry', 'pastry')
                       .replace('creamcream', 'cream')
                       .replace('doublecrustpiepastry', 'pastry')
                       .replace('unsaltedbutter', 'butter')
                       .replace('allpurposeflour', 'flour')
                       .replace('bakingpowder', 'baking powder')
                       .replace('bakingsoda', 'baking soda')
                       .replace('currypowder', 'curry powder')
                       .replace('maplesyrup', 'maple syrup')
                       .replace('lightcornsyrup', 'corn syrup')
                       .replace('cornsyrup', 'corn syrup')
                       .replace('wholeallspice', 'allspice')
                       .replace('wholecloves', 'clove')
                       .replace('flakedcoconut', 'coconut')
                       .replace('shreddedcoconut', 'coconut')
                       .replace('beefbouillonpowder', 'beef bouillon')
                       .replace('cocoapowder', 'cocoa powder')
                       .replace('cayennepepper', 'cayenne pepper')
                       .replace('chickenbroth', 'chicken broth')
                       .replace('whiterice', 'rice')
                       .replace('cornflakescereal', 'corn flakes')
                       .replace('crushedcornflakes', 'corn flakes')
                       .replace('crispyricecereal', 'rice cereal')
                       .replace('stalkscelery', 'celery')
                       .replace('pecanhalves', 'pecan')
                       .replace('goldpotatoes', 'potato')
                       .replace('medjool', 'date')
                       .replace('basmati', 'rice')
                       .replace('saffronthreads', 'saffron')
                       .replace('grapeseedoil', 'oil')
                       .replace('goldenraisins', 'raisin')
                       .replace('woodenchopsticksforhandles', '')
                       .replace('quarterrounds', ''))
            dynamic_map[ingredient] = cleaned

    return dynamic_map


def normalize_ingredient_name(ingredient_str):
    """Normalizza il nome dell'ingrediente, mappandolo al nome base."""
    ingredient_str = ingredient_str.lower().strip()
    for base_name, normalized_name in base_ingredients_map.items():
        if base_name in ingredient_str:
            return normalized_name
    # Rimuovi descrizioni, frazioni, quantità, unità, parentesi e caratteri speciali
    cleaned = re.sub(removal_pattern, '', ingredient_str, flags=re.IGNORECASE).strip()
    # Normalizza spazi multipli
    cleaned = ' '.join(cleaned.split()).strip()
    # Correggi termini concatenati (esteso)
    cleaned = (cleaned.replace('brownsugar', 'brown sugar')
               .replace('grannysmithapples', 'apple')
               .replace('macintoshapples', 'apple')
               .replace('puffpastry', 'pastry')
               .replace('creamcream', 'cream')
               .replace('doublecrustpiepastry', 'pastry')
               .replace('unsaltedbutter', 'butter')
               .replace('allpurposeflour', 'flour')
               .replace('bakingpowder', 'baking powder')
               .replace('bakingsoda', 'baking soda')
               .replace('currypowder', 'curry powder')
               .replace('maplesyrup', 'maple syrup')
               .replace('lightcornsyrup', 'corn syrup')
               .replace('cornsyrup', 'corn syrup')
               .replace('wholeallspice', 'allspice')
               .replace('wholecloves', 'clove')
               .replace('flakedcoconut', 'coconut')
               .replace('shreddedcoconut', 'coconut')
               .replace('beefbouillonpowder', 'beef bouillon')
               .replace('cocoapowder', 'cocoa powder')
               .replace('cayennepepper', 'cayenne pepper')
               .replace('chickenbroth', 'chicken broth')
               .replace('whiterice', 'rice')
               .replace('cornflakescereal', 'corn flakes')
               .replace('crushedcornflakes', 'corn flakes')
               .replace('crispyricecereal', 'rice cereal')
               .replace('stalkscelery', 'celery')
               .replace('pecanhalves', 'pecan')
               .replace('goldpotatoes', 'potato')
               .replace('medjool', 'date')
               .replace('basmati', 'rice')
               .replace('saffronthreads', 'saffron')
               .replace('grapeseedoil', 'oil')
               .replace('goldenraisins', 'raisin')
               .replace('woodenchopsticksforhandles', '')
               .replace('quarterrounds', ''))
    return cleaned if cleaned else None


def parse_ingredients(ingredients_str):
    """Estrae e normalizza gli ingredienti, restituendo una stringa separata da virgole."""
    if not isinstance(ingredients_str, str) or not ingredients_str.strip():
        return ""

    ingredients = [i.strip() for i in ingredients_str.split(',')]
    normalized = []
    for ingredient in ingredients:
        cleaned = re.sub(removal_pattern, '', ingredient, flags=re.IGNORECASE).strip()
        cleaned = ' '.join(cleaned.split()).strip()
        normalized_name = normalize_ingredient_name(cleaned)
        if normalized_name and normalized_name not in normalized:
            normalized.append(normalized_name)

    return ", ".join(normalized)


def extract_all_ingredients(df):
    """Estrae tutti gli ingredienti unici dal dataset."""
    all_ingredients = []
    for ingredients_str in df['ingredients']:
        if not isinstance(ingredients_str, str):
            continue
        ingredients = [i.strip() for i in ingredients_str.split(',')]
        for ingredient in ingredients:
            cleaned = re.sub(removal_pattern, '', ingredient, flags=re.IGNORECASE).strip()
            cleaned = ' '.join(cleaned.split()).strip()
            if cleaned:
                all_ingredients.append(cleaned.lower())

    return all_ingredients


def check_mapping_coverage(all_ingredients):
    """Verifica quali ingredienti non sono coperti dalla mappatura."""
    unmapped_ingredients = []
    for ingredient in set(all_ingredients):
        normalized = normalize_ingredient_name(ingredient)
        if normalized and normalized not in base_ingredients_map.values():
            if not any(base_name in ingredient for base_name in base_ingredients_map):
                unmapped_ingredients.append(ingredient)

    return unmapped_ingredients


# Leggi l'intero dataset
df = pd.read_csv("recipes.csv")

# Estrai tutti gli ingredienti unici
all_ingredients = extract_all_ingredients(df)

# Genera una mappatura dinamica per ingredienti frequenti
dynamic_map = generate_dynamic_mapping(all_ingredients, min_freq=1)
base_ingredients_map.update(dynamic_map)

# Verifica la copertura della mappatura aggiornata
unmapped_ingredients = check_mapping_coverage(all_ingredients)

# Crea la colonna base_ingredients
df['base_ingredients'] = df['ingredients'].apply(parse_ingredients)

# Salva il DataFrame in un nuovo file CSV
df.to_csv("recipes.csv", index=False)

# Stampa statistiche
print(f"Totale ricette processate: {len(df)}")
print(f"Ricette con base_ingredients vuota: {len(df[df['base_ingredients'] == ''])}")
print(f"Totale ingredienti unici trovati: {len(set(all_ingredients))}")
print(f"Ingredienti non coperti dalla mappatura aggiornata: {len(unmapped_ingredients)}")
if unmapped_ingredients:
    print("Esempi di ingredienti non coperti (primi 10):")
    print(unmapped_ingredients[:10])
else:
    print("Tutti gli ingredienti sono coperti dalla mappatura aggiornata.")

# Stampa le prime tre righe per verifica
print("\nPrime tre righe di 'base_ingredients':")
for index, row in df.head(3).iterrows():
    print(f"Ricetta: {row['recipe_name']}")
    print(f"base_ingredients: {row['base_ingredients']}")

# Stampa i primi 10 ingredienti della mappatura dinamica aggiunti:
print("\nPrimi 10 ingredienti della mappatura dinamica aggiunti:")
for k, v in list(dynamic_map.items())[:10]:
    print(f"{k} -> {v}")