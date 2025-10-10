import pandas as pd
import re
import inflect

# Inizializza l'engine di inflect per convertire plurali in singolari
p = inflect.engine()

# Lista estesa di parole descrittive da rimuovere
descriptive_words = [
    'chopped', 'diced', 'minced', 'sliced', 'fresh', 'dried', 'ground', 'crushed',
    'grated', 'peeled', 'cooked', 'raw', 'frozen', 'canned', 'finely', 'coarsely',
    'whole', 'boneless', 'skinless', 'lean', 'extra', 'virgin', 'unsalted', 'salted',
    'sweet', 'brown', 'white', 'red', 'black', 'green', 'yellow', 'organic', 'roasted',
    'toasted', 'smoked', 'shredded', 'large', 'small', 'medium', 'crumbled', 'melted',
    'softened', 'packed', 'pureed', 'gluten-free', 'low-fat', 'fat-free', 'free-range',
    'shelled', 'unshelled', 'pitted', 'seedless', 'halved', 'quartered', 'cubed',
    'julienned', 'sprig', 'sprigs', 'leaf', 'leaves', 'grinded', 'natural', 'processed',
    'seeded', 'rinsed', 'reserved', 'lengthwise', 'deveined', 'husked', 'tidbit',
    'roughy', 'temperature', 'fit', 'creations', 'optional', 'diced', 'sliced',
    'high', 'quality', 'unsweetened', 'old', 'fashioned', 'quick', 'cooking', 'confectioners',
    'divided', 'or', 'as', 'needed', 'to', 'taste', 'thick', 'thinly', 'cored', 'quartered',
    'inch', 'that', 'fall', 'apart', 'when', 'other', 'firm', 'crisp', 'soft', 'textured',
    'lightly', 'sweetened', 'whipped', 'more', 'such', 'blanched', 'pitted', 'coarsely',
    'roughly', 'boiling', 'cold', 'roasted'
]

# Lista estesa di ingredienti multi-parola da preservare
multi_word_ingredients = [
    'brown sugar', 'white sugar', 'powdered sugar', 'granulated sugar', 'cane sugar',
    'olive oil', 'coconut oil', 'vegetable oil', 'canola oil', 'sesame oil', 'sunflower oil',
    'chicken breast', 'chicken thigh', 'chicken wing', 'chicken drumstick', 'pork chop',
    'coconut milk', 'almond milk', 'soy milk', 'oat milk', 'heavy cream', 'sour cream',
    'cream cheese', 'greek yogurt', 'plain yogurt', 'soy sauce', 'fish sauce', 'oyster sauce',
    'hoisin sauce', 'worcestershire sauce', 'tomato paste', 'tomato sauce', 'pizza sauce',
    'chicken broth', 'beef broth', 'vegetable broth', 'bone broth', 'maple syrup',
    'honey', 'molasses', 'peanut butter', 'almond butter', 'cashew butter',
    'vanilla extract', 'almond extract', 'lemon extract', 'baking powder', 'baking soda',
    'apple cider', 'apple juice', 'orange juice', 'lemon juice', 'lime juice',
    'red wine', 'white wine', 'rice vinegar', 'balsamic vinegar', 'apple cider vinegar',
    'sweet potato', 'green bean', 'black bean', 'kidney bean', 'pinto bean', 'navy bean',
    'ice cream', 'whipped cream', 'condensed milk', 'evaporated milk', 'sea salt',
    'table salt', 'kosher salt', 'black pepper', 'white pepper', 'cayenne pepper',
    'red pepper', 'chili powder', 'curry powder', 'garlic powder', 'onion powder',
    'sesame seed', 'chia seed', 'flax seed', 'poppy seed', 'cocoa powder',
    'crispy rice cereal', 'rice cereal', 'confectioners sugar', 'granny smith apple',
    'honeycrisp apple', 'braeburn apple', 'fuji apple', 'gala apple', 'pink lady apple',
    'jonagold apple', 'mcintosh apple', 'golden delicious apple', 'cortland apple',
    'empire apple', 'rome apple', 'bramley apple', 'gravenstein apple'
]

# Mappatura per eccezioni di singularizzazione
singular_exceptions = {
    'geese': 'goose',
    'dice': 'die',
    'lice': 'louse',
    'mice': 'mouse',
    'feet': 'foot',
    'teeth': 'tooth'
}


def clean_and_singularize_ingredient(ingredient_str):
    if not ingredient_str or not isinstance(ingredient_str, str):
        return None
    # Converti in minuscolo e rimuovi spazi iniziali/finali
    ingredient_str = ingredient_str.strip().lower()
    # Rimuovi contenuti tra parentesi
    ingredient_str = re.sub(r'\s*\([^)]*\)', '', ingredient_str)
    # Rimuovi numeri e termini come "type 00" o "grade A"
    ingredient_str = re.sub(r'\b(type|grade)\s+\w+\b', '', ingredient_str)
    # Rimuovi punteggiatura e caratteri speciali
    ingredient_str = re.sub(r'[^\w\s]', '', ingredient_str)
    # Normalizza spazi multipli
    ingredient_str = re.sub(r'\s+', ' ', ingredient_str).strip()

    # Salta se è '-' o vuoto
    if not ingredient_str or ingredient_str == '-':
        return None

    # Controlla se l'ingrediente corrisponde a un ingrediente multi-parola noto
    for multi_word in multi_word_ingredients:
        # Cerca corrispondenza esatta o pluralizzata
        multi_word_lower = multi_word.lower()
        # Prova a singularizzare l'ultima parola per gestire plurali
        words = multi_word_lower.split()
        if len(words) > 1:
            last_word = words[-1]
            singular_last = p.singular_noun(last_word) or last_word
            singular_multi = ' '.join(words[:-1] + [singular_last])
            multi_variations = [multi_word_lower, singular_multi]
        else:
            multi_variations = [multi_word_lower]

        for variation in multi_variations:
            if variation in ingredient_str:
                # Rimuovi parole descrittive che precedono o seguono
                cleaned = ingredient_str
                for desc in descriptive_words:
                    cleaned = re.sub(rf'\b{desc}\b', '', cleaned, flags=re.IGNORECASE).strip()
                # Normalizza spazi dopo rimozione
                cleaned = re.sub(r'\s+', ' ', cleaned).strip()
                # Verifica che l'ingrediente multi-parola sia ancora presente
                if variation in cleaned:
                    # Usa la forma singolare dell'ingrediente multi-parola
                    return singular_multi if variation == singular_multi else multi_word_lower

    # Se non è un ingrediente multi-parola, rimuovi parole descrittive
    words = ingredient_str.split()
    if not words:
        return None
    # Filtra parole descrittive e numeri puri
    core_words = [word for word in words if word not in descriptive_words and not re.match(r'^\d+$', word)]
    if not core_words:
        return None

    # Prendi l'ultima parola non descrittiva come ingrediente principale
    core_ingredient = core_words[-1]
    # Controlla eccezioni di singularizzazione
    if core_ingredient in singular_exceptions:
        return singular_exceptions[core_ingredient]
    # Converti in singolare
    singular_ingredient = p.singular_noun(core_ingredient) or core_ingredient
    return singular_ingredient


def extract_single_ingredients(parsed_str):
    if not parsed_str or not isinstance(parsed_str, str):
        return ''
    # Dividi gli ingredienti su ';'
    ingredients = parsed_str.split(';')
    single_ingredients = []
    for ing in ingredients:
        # Cerca il testo dopo 'ingredient:'
        match = re.search(r'ingredient:\s*([^;]+)', ing.strip())
        if match:
            ingredient_text = match.group(1).strip()
            if ingredient_text == '-' or not ingredient_text:
                continue
            cleaned_ingredient = clean_and_singularize_ingredient(ingredient_text)
            if cleaned_ingredient:
                single_ingredients.append(cleaned_ingredient)
    # Rimuovi duplicati mantenendo l'ordine
    single_ingredients = list(dict.fromkeys(single_ingredients))
    # Formatta come lista ingrediente1, ingrediente2, ...
    return ', '.join(single_ingredients) if single_ingredients else ''


# Carica il dataset
df = pd.read_csv("recipes.csv")
df = df.dropna(subset=['ingredients_parsed'])

# Crea la nuova colonna 'single_ingredients'
df['single_ingredients'] = df['ingredients_parsed'].apply(extract_single_ingredients)

# Stampa il DataFrame con le colonne rilevanti per verifica
print(df[['recipe_name', 'ingredients_parsed', 'single_ingredients']])

# Salva il DataFrame aggiornato
df.to_csv("recipes.csv", index=False)
print("\nDataFrame salvato in 'recipes.csv'")