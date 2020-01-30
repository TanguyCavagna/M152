const search = document.querySelector('.search-bar input[type="search"]');
const searchResults = document.querySelector('.search-results');

/**
 * Search articles.json and filter it
 * @param {string} searchText Search-bar value
 */
const searchArticles = async searchText => {
    const res = await fetch('../json/articles.json');
    const articles = await res.json();

    // Get matches to current text input
    let matches = articles.filter(article => {
        const regex = new RegExp(`^${searchText.replace('\'', '&#39;')}`, 'gi');
        return article.title.match(regex) || article.author.match(regex);
    });

    if (searchText.length === 0) {
        matches = [];
    }

    outputMatches(matches, searchText);
};

/**
 * Output all the matches as the serach results
 * @param {array} matches List of all matches
 */
const outputMatches = (matches, searchText) => {
    if (matches.length > 0) {
        searchResults.classList.remove('hide');

        const html = matches.map(match => `
            <div>${match.title} - ${match.resume} - ${match.author} </div>
        `).join('');

        searchResults.innerHTML = '';
        matches.forEach(match => {
            let category = document.querySelector(`.search-results .${match.category.split(' ')[0].toLowerCase()}`);
            if (category === null) {
                searchResults.innerHTML += `
                <div class="search-results__category ${match.category.split(' ')[0].toLowerCase()}">
                    <h2>${match.category}</h2>
                    <div class="search-results__category-elements"></div>
                </div>`;
            }

            const regex = new RegExp(`^${searchText.replace('\'', '&#39;')}`, 'gi'); // To find the highligthed text
            
            category = document.querySelector(`.search-results .${match.category.split(' ')[0].toLowerCase()} .search-results__category-elements`);
            let highlightTitle = match.title.match(regex);
            let highlightAuthor = match.author.match(regex);
            category.innerHTML += `
                <div class="search-result">
                    <span class="search-result__title">${highlightTitle !== null ? `<span class="search-result__highlight">${highlightTitle[0]}</span>${match.title.substring(highlightTitle[0].length, match.title.length)}` : match.title}</span>
                    <span class="search-result__resume">${match.resume}</span>
                    <span class="search-result__author">${highlightAuthor !== null ? `<span class="search-result__highlight">${highlightAuthor[0]}</span>${match.author.substring(highlightAuthor[0].length, match.author.length)}` : match.author}</span>
                </div>`;
        });
    } else {
        if (searchText.length == 0) {
            searchResults.classList.add('hide');
            searchResults.innerHTML = '';
        } else {
            searchResults.classList.remove('hide');
            searchResults.innerHTML = `
                <div class="no-result">
                    No results found for query "<span class="no-result__query">${searchText}</span>"
                </div>
            `;
        }
    }
};

search.addEventListener('input', () => searchArticles(search.value));

document.addEventListener('click', e => {
    // If user clicks inside the search-results, do nothing
    if (e.target.closest('.search-results')) return;

    // If users clicks outside the search-results, hide it
    searchResults.classList.add('hide');
});