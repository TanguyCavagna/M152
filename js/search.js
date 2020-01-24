const search = document.querySelector('.search-bar input[type="search"]');

/**
 * Search articles.json and filter it
 * @param {string} searchText Search-bar value
 */
const searchArticles = async searchText => {
    const res = await fetch('./json/articles.json');
    const articles = await res.json();

    // Get matches to current text input
    let matches = articles.filter(article => {
        const regex = new RegExp(`^${searchText}`, 'gi');
        return article.title.match(regex) || article.author.match(regex);
    });

    if (searchText.length === 0) {
        matches = [];
    }

    outputMatches(matches);
};

/**
 * Output all the matches as the serach results
 * @param {array} matches List of all matches
 */
const outputMatches = matches => {
    if (matches.length > 0) {
        const html = matches.map(match => `
            <div>${match.title} - ${match.resume} - ${match.author} </div>
        `).join('');
        
        // TODO: Create an absolute div to put those results
        console.log(html);
    }
};

search.addEventListener('input', () => searchArticles(search.value));