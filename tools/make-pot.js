'use strict';

const fs = require('fs');
const wpPot = require('wp-pot');

wpPot({
	destFile: 'inkwell/languages/inkwell.pot',
	domain: 'inkwell',
	package: 'Inkwell 2.0.5',
	src: ['inkwell/**/*.php', 'inkwell/style.css'],
	metadataFile: 'style.css',
	relativeTo: 'inkwell',
	bugReport: 'https://github.com/smohamadth/Bookstore_WC/issues',
	copyrightText:
		'# Copyright (C) 2026 Inkwell Studio\n# This file is distributed under the GNU General Public License v2 or later.',
	includePOTCreationDate: false,
	lastTranslator: 'FULL NAME <EMAIL@ADDRESS>',
	team: 'LANGUAGE <LL@li.org>',
});

const output = fs.readFileSync('inkwell/languages/inkwell.pot', 'utf8').replace(/\n+$/, '\n');
fs.writeFileSync('inkwell/languages/inkwell.pot', output);
