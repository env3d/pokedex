const poke_container = document.getElementById('poke_container');
const pokemons_number = 99;
const colors = {
    fire: '#FDDFDF',
    grass: '#DEFDE0',
    electric: '#FCF7DE',
    water: '#DEF3FD',
    ground: '#f4e7da',
    rock: '#d5d5d4',
    fairy: '#fceaff',
    poison: '#98d7a5',
    bug: '#f8d5a3',
    dragon: '#97b3e6',
    psychic: '#eaeda1',
    flying: '#F5F5F5',
    fighting: '#E6E0D4',
    normal: '#F5F5F5'
};

const main_types = Object.keys(colors);
console.log(main_types);

// login_script_el = document.createElement('script');
// login_script_el.src = `src="https://apis.google.com/js/platform.js"`
// login_script_el.async = true;
// login_script_el.defer = true;
// login_meta_el = document.createElement('meta');
// login_meta_el.name = "google-signin-client_id";
// login_meta_el.content = "107256413984-r8i468m63oe3afq55gc5aoto78voelpi.apps.googleusercontent.com";    
// document.head.append(login_script_el);
// document.head.append(login_meta_el);

// login_button_el = document.createElement('div');
// login_button_el.classList.add("g-signin2");
// login_button_el['data-onsuccess'] = "onSignIn";
// document.body.prepend(login_button_el);

function createPokemonCard(pokemon) {
    const pokemonEl = document.createElement('div');
    pokemonEl.classList.add('pokemon');

    const poke_types = pokemon.types.map(type => type.type.name);
    const abilities = pokemon.abilities.map( ability => ability.ability.name )
    const type = main_types.find(type => poke_types.indexOf(type) > -1);
    const name = pokemon.name[0].toUpperCase() + pokemon.name.slice(1);
    const color = colors[type];

    pokemonEl.style.backgroundColor = color;
    subpath = document.location.href.search('pokemon') == -1 ? "" : "../"
    const pokeInnerHTML = `        
        <div class="img-container">
            <img 
                src="${subpath}images/${pokemon.id}.png" 
                alt="${name}"
            />
        </div>
        <div class="info">
            <span class="number">
                <!-- padStart(3, '0') means the length of number is 3 but it starts with 0 (e.g. : 001) -->
                #${pokemon.id.toString().padStart(3, '0')}
            </span>
            <h3 class="name">${name}</h3>
            <small class="type">
                Type: <span>${type}</span>
            </small>
            <div class="description"></div>
        </div>         
    `;

    pokemonEl.innerHTML = pokeInnerHTML;
    return pokemonEl
}

const getPokemon = async id => {
    const url = `https://pokeapi.co/api/v2/pokemon/${id}`;
    const res = await fetch(url);
    const pokemon = await res.json();
    console.log(pokemon);
    return pokemon;    
}

const getPokemonDescription = async id => {
    const url = `https://pokeapi.co/api/v2/pokemon-species/${id}`;
    const res = await fetch(url);
    const desc = await res.json();
    console.log(desc);
    //return desc;
    const s = 
        new Map(desc['flavor_text_entries']
            .filter(item => item['language']['name'] == 'en')
            .map( item => {
                text = item['flavor_text'];
                return [text.replace(/[\u0000-\u001F\u007F-\u009F ]/g,"").toLowerCase(), text]
            } )
        )
    console.log(s)
    return Array.from(s.values());
}

const fetchPokemons = async() => {
    // index(i) must start from 1 as id=0 is undefined. 
    for (let i = 1; i <= pokemons_number; i++) {
        pokemon = await getPokemon(i);
        el = createPokemonCard(pokemon);
        html_link = `<a href="pokemon/${i}.html">` + el.innerHTML +`</a>`
        php_link = `<a href="index.php?id=${i}">` + el.innerHTML +`</a>`        
        el.innerHTML = location.pathname.endsWith('html') ? html_link : php_link;
        poke_container.appendChild(el);        
    }
}


