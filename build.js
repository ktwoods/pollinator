

/* Builds the contents for the main wildlife category pages. Currently, this
   breaks the category apart into tabs separated by family (bee, butterfly/moth)
   or type (everything else, a broad enough category that division by family is
   far too granular), and lists the members of each family/type in a simple
   table for each tab.
*/
function buildTabsByCategory(container, categoryList, speciesList, tableClass) {
  categoryList.push({'family_name': 'All'});

  let pillList = document.createElement('ul');
  $(pillList).attr({
    'class': 'nav nav-pills justify-content-center',
    'id': 'pills-tab',
    'role': 'tablist'
  });
  container.append(pillList);

  let allTabContent = document.createElement('div');
  allTabContent.className = 'tab-content';
  allTabContent.id =  'tabContent';
  container.append(allTabContent);

  // build pills
  for (let i = 0; i < categoryList.length; i++) {
    let categoryName = categoryList[i]['family_name'] || categoryList[i]['type'];
    let categoryDesc = categoryList[i]['family_desc'];
    let id = (categoryName === 'All' ? container.id : categoryName);

    // build pills
    let pill = document.createElement('li');
    pill.className = 'nav-item';
    pillList.append(pill);

    let pillLink = document.createElement('a');
    pill.append(pillLink);
    if (i === 0) pillLink.toggleAttribute('active');
    $(pillLink).attr({
      'class': 'nav-link' + (i === 0 ? ' active' : ''),
      'data-toggle': 'pill',
      'id': id + 'Tab',
      'href': '#' + id + 'Content',
      'aria-controls': id + '-tab',
      'aria-selected': (i === 0)
    });
    pillLink.textContent = (categoryName === 'All' ? '(All)' : categoryName);

    // build pill tab contents
    let tabContent = document.createElement('div');
    $(tabContent).attr({
      'class': 'tab-pane fade' + (i === 0 ? ' show active' : ''),
      'id': id + 'Content',
      'role': 'tabpanel',
      'aria-labelledby': id + '-tab'
    });
    allTabContent.append(tabContent);
    let tabHeader = document.createElement('h3');
    tabHeader.className = 'text-center';
    if (categoryDesc) tabHeader.textContent = `${categoryName} (${categoryDesc})`;
    else tabHeader.textContent = (categoryName === 'All' ? 'All species' : categoryName);
    tabContent.append(tabHeader);

    // filter species list down to creatures in that family, then go build table
    let filteredSpecies = [];
    if (tableClass === 'o') {
      filteredSpecies = speciesList.filter(sp => categoryName === 'All' || sp['type'] === categoryName);
      for (let sp of filteredSpecies) {
        delete sp['type'];
      }
    }
    else {
      for (let sp of speciesList) {
        if (categoryName === 'All' || categoryName === sp['family_name']) {
          if (categoryName !== 'All') delete sp['family_name'];
          filteredSpecies.push(sp);
        }
      }
    }
    tabContent.append(speciesTable(filteredSpecies, tableClass));
  }
}

function speciesTable(speciesList, tableClass) {
  let table = document.createElement('table');
  table.style.width = '80%';

  // header row
  let head = document.createElement('thead');
  table.append(head);
  let row = document.createElement('tr');
  head.append(row);
  for (let key in speciesList[0]) {
    let h = document.createElement('th');
    if (key !== 'img_url') h.textContent = formatPHPKey(key);
    row.append(h);
  }

  // data rows
  let body = document.createElement('tbody');
  table.append(body);
  for (let species of speciesList) {
    row = document.createElement('tr');
    body.append(row);

    // row color coding
    if (species['sightings'] > 0) {
      if (species['latin_name'].split(' ')[1] === 'spp') row.className = `seen-${tableClass}-genus`;
      else row.className = 'seen-' + tableClass;
    }

    // cells
    for (let key in species) {
      let cell = document.createElement('td');
      // make image a thumbnaiil
      if (key === 'img_url') {
        if (tableClass === 'p') cell.append(thumbnail(species[key], species['latin_name'], undefined, 'view_plant.php'));
        else cell.append(thumbnail(species[key], species['latin_name']));
      }
      // make latin name italicized (but not <em>, as it's not semantic emphasis)
      else if (key === 'latin_name') {
        if (tableClass === 'p') cell.innerHTML = `<a href="view_plant.php?spp=${species[key]}"><i>${species[key]}</i></a>`;
        else cell.innerHTML = `<a href="view.php?spp=${species[key]}"><i>${species[key]}</i></a>`;
      }
      // display booleans as check mark or dash
      else if (key === 'have' || key === 'want') {
        cell.className = 'text-center';
        cell.innerHTML = (+species[key] ? '&#x2713' : '&mdash;');
      }
      else cell.textContent = species[key];
      row.append(cell);
    }
  }
  return table;
}

function formatPHPKey(key) {
  key = key.charAt(0).toUpperCase() + key.slice(1);
  return key.replace('_', ' ');
}

/* Builds tiny thumbnail for species tables. If there's no image, it substitutes
   a gray box of the same size. In either case, the thumbnail links to the species page. */
function thumbnail(url, latinName, size='2rem', page='view.php', tooltip) {
  let thumbnail = document.createElement('a');

  // Imgur uses multiple urls per image, making it convenient to reduce sizes for faster loading times
  if (url && url.includes('i.imgur.com')) url = url.replace('l.', 't.');

  thumbnail.href = `${page}?spp=${latinName}`;
  if (tooltip) {
    thumbnail.setAttribute('data-toggle') = 'tooltip';
    thumbnail.setAttribute('data-placement') = 'right';
    thumbnail.title = tooltip;
  }

  let defaultBox = document.createElement('div');
  thumbnail.append(defaultBox);
  defaultBox.style.width = defaultBox.style.height = size;
  defaultBox.style.backgroundColor = '#e9ecef';
  defaultBox.style.display = 'inline-block';
  defaultBox.style.verticalAlign = 'middle';

  if (url) defaultBox.innerHTML = `<img src="${url}" style="max-width:100%; max-height: 100%">`

  return thumbnail;
}
