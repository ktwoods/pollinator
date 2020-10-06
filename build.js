/* Builds the contents for the main wildlife category pages. Currently, this
   breaks the category apart into tabs separated by family (bee, butterfly/moth)
   or type (everything else, a broad enough category that division by family is
   far too granular), and lists the members of each family/type in a simple
   table for each tab. */
function buildTabsByCategory(categoryAttr, categoryList, speciesList, viewURL='view.php') {
  let pillList = $('<ul/>', {
    'class': 'nav nav-pills justify-content-center',
    'id': 'pills-tab',
    'role': 'tablist'
  });
  let allTabContent = $('<div/>', {'class': 'tab-content', 'id': 'tabContent'});

  // build pills
  for (let i = 0; i < categoryList.length; i++) {
    let categoryName = categoryList[i][categoryAttr];
    let categoryDesc = categoryList[i]['family_desc'] || null;
    let id = (categoryName === 'All' ? categoryDesc : categoryName).replace(/[^\w]/g, '');

    // build pills
    let pill = $('<li/>', {'class': 'nav-item'});
    pillList.append(pill);

    let pillLink = $('<a/>', {
      'class': 'nav-link' + (i === 0 ? ' active' : ''),
      'data-toggle': 'pill',
      'id': id + 'Tab',
      'href': '#' + id + 'Content',
      'aria-controls': id + '-tab',
      'aria-selected': (i === 0)
    });
    pillLink.append(categoryName === 'All' ? '(All)' : categoryName);
    pill.append(pillLink);

    // build pill tab contents
    let tabContent = $('<div/>', {
      'class': 'tab-pane fade' + (i === 0 ? ' show active' : ''),
      'id': id + 'Content',
      'role': 'tabpanel',
      'aria-labelledby': id + '-tab'
    });
    allTabContent.append(tabContent);

    let tabHeader = $('<h3/>', {'class': 'text-center'});
    if (categoryName === 'All') tabHeader.append('All species');
    else if (categoryDesc) tabHeader.append(`${categoryName} (${categoryDesc})`);
    else tabHeader.append(categoryName);
    tabContent.append(tabHeader);

    // filter species list down to creatures in that category, then go build table
    let filteredSpecies = [];

    for (let sp of speciesList) {
      if (categoryName === 'All' || categoryName === sp[categoryAttr]) {
        filteredSpecies.push(sp);
        if (categoryName !== 'All') delete filteredSpecies[filteredSpecies.length - 1][categoryAttr];
      }
    }
    tabContent.append(table(filteredSpecies, viewURL));
  }
  return $('<div/>').append(pillList, allTabContent);
}

/* Builds and returns a generic table */
function table(list, viewURL='view.php') {
  let table = $('<table/>');
  // header row
  let rowString = '';
  for (let key in list[0]) {
    rowString += '<th>';
    if (key !== 'img_url') rowString += key.charAt(0).toUpperCase() + key.replace('_', ' ').slice(1);
    rowString += '</th>';
  }
  table.append('<thead>' + rowString + '</thead>');

  // data rows
  let body = $('<tbody/>');
  for (let item of list) {
    row = $('<tr/>');
    body.append(row);
    // row color coding
    if (item['sightings'] > 0) {
      if (item['latin_name'].split(' ')[1] === 'spp') row.addClass('seen-genus');
      else row.addClass('seen');
    }
    // cells
    for (let key in item) {
      let cell = item[key];
      switch (key) {
        // make image a thumbnaiil
        case ('img_url'): cell = thumbnail(item[key], item['latin_name'], undefined, viewURL); break;
        // make latin name italicized (but not <em>, as it's not semantic emphasis)
        case ('latin_name'): cell = `<a href="${viewURL}?sp=${item[key]}"><i>${item[key]}</i></a>`; break;
        // display booleans as check mark or dash
        case ('have'):
        case ('want'): cell = '<span class="text-center">' + (+item[key] ? '&#x2713' : '&mdash;') + '</span>'; break;
      }
      row.append($('<td/>').append(cell));
    }
  }
  table.append(body);
  return table;
}

/* Builds and returns tiny thumbnail for species tables. If there's no image, it substitutes
   a gray box of the same size. In either case, the thumbnail links to the species page. */
function thumbnail(imageURL, latinName, size='2rem', pageURL='view.php', tooltip) {
  let thumbnail = $('<a/>', {'href': `${pageURL}?sp=${latinName}`});
  if (tooltip) {
    thumbnail.attr({'data-toggle': 'tooltip', 'data-placement': 'right', 'title': tooltip});
  }
  // Imgur uses multiple urls per image, making it convenient to reduce sizes for faster loading times
  if (imageURL && imageURL.includes('i.imgur.com')) imageURL = imageURL.replace('l.', 't.');

  thumbnail.append(`<div style="width: ${size}; height: ${size}; background-color: #e9ecef; display: inline-block; vertical-align: middle">` + (imageURL ? `<img src="${imageURL}" style="max-width:100%; max-height: 100%">` : '') + '</div>');
  return thumbnail;
}

/* Returns jQuery object containing a badge that produces a popover on hover */
function countBadgePopover(logs) {
  if (logs.length === 0) return '<span class="badge badge-light">0</span></td>';

  const badge = $('<a/>', {
    'href': '#',
    'data-toggle': 'popover',
    'data-trigger': 'hover',
    'data-html': 'true',
    'data-placement': 'top',
    'data-content': logs.reduce((acc, cur) =>
      acc + '<div><strong>' + cur['date'] + ':</strong> ' + cur['notes'] + '</div>'
    , '')
  });
  badge.html('<span class="badge badge-dark">' + logs.length + '</span>');
  return badge;
}

/* Builds and returns alert indicating whether changes were successfully made to the database. */
function changeAlert(success, successMessage, failMessage='No changes made.') {
  let alert = document.createElement('div');
  alert.className = 'alert alert-success alert-dismissable text-center';
  alert.role = 'alert';
  alert.innerHTML = (success ? successMessage : failMessage);
  let dismissButton = document.createElement('button');
  alert.append(dismissButton);
  $(dismissButton).attr({
    'type': 'button',
    'class': 'close',
    'data-dismiss': 'alert',
    'aria-label': 'close'
  });
  dismissButton.innerHTML = '<span aria-hidden="true">&times;</span>';

  return alert;
}

/* Builds logbook */
function logbook(logs) {
  let header = $('<div/>', {'class':'card-header mb-0', 'id':'logHeader', 'data-toggle':'collapse', 'data-target':'#logs'});
  header.html(`<i class="fas fa-caret-down"></i> <span class="badge badge-${logs.length ? 'dark' : 'light'}">${logs.length}</span> <strong>Logbook</strong>`);
  let body = $('<div id="logs" class="collapse card-body" aria-labelledby="logHeader"></div>');
  if (logs.length) body.append(table(logs));

  return $('<div class="card"></div>').append(header, body);
}

/* Builds a list of months (e.g. months a plant is in bloom) with tooltips and returns as a string */
function monthTooltips(months) {
  console.log(months);
  if (months.length === 0) return 'n/a';
  let monthHTML = '';
  for (let i = 0; i < months.length; i++) {
    let m = months[i];
    monthHTML += '<a href="#" data-toggle="tooltip" title="';
    if (+m['verified']) {
      monthHTML += (m.notes ? m.notes : 'n/a') + '" data-placement="top">' + m.month.substr(0, 3) + '</a>';
    }
    else {
      monthHTML += '[Unverified] ' + m.notes + '" data-placement="top"><em>' + m.month.substr(0, 3) + '</em></a>';
    }
    if (i != months.length - 1) monthHTML += ' – ';
  }
  return monthHTML;
}
