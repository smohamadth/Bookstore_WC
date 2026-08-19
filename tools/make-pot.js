'use strict';

const fs = require('fs');
const wpPot = require('wp-pot');

wpPot({
	destFile: 'inkwell/languages/inkwell.pot',
	domain: 'inkwell',
	package: 'Inkwell 2.0.9',
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

const themePot = 'inkwell/languages/inkwell.pot';
let themeOutput = fs.readFileSync(themePot, 'utf8').replace(/\n+$/, '\n');
const themeJson = JSON.parse(fs.readFileSync('inkwell/theme.json', 'utf8'));
const themeJsonStrings = [
	...themeJson.settings.color.palette.map((item) => ['Color name', item.name]),
	...themeJson.settings.typography.fontFamilies.map((item) => ['Font family name', item.name]),
];
for (const [context, message] of themeJsonStrings) {
	if (!themeOutput.includes(`msgid ${JSON.stringify(message)}\n`)) {
		themeOutput += `\n#: theme.json\nmsgctxt ${JSON.stringify(context)}\nmsgid ${JSON.stringify(message)}\nmsgstr ""\n`;
	}
}
fs.writeFileSync(themePot, themeOutput.replace(/\n+$/, '\n'));

fs.mkdirSync('inkwell-books/languages', { recursive: true });
wpPot({
	destFile: 'inkwell-books/languages/inkwell-books.pot',
	domain: 'inkwell-books',
	package: 'Inkwell Books 1.0.0',
	src: ['inkwell-books/**/*.php'],
	metadataFile: 'inkwell-books.php',
	relativeTo: 'inkwell-books',
	bugReport: 'https://github.com/smohamadth/Bookstore_WC/issues',
	copyrightText:
		'# Copyright (C) 2026 Inkwell Studio\n# This file is distributed under the GNU General Public License v2 or later.',
	includePOTCreationDate: false,
	lastTranslator: 'FULL NAME <EMAIL@ADDRESS>',
	team: 'LANGUAGE <LL@li.org>',
});
const pluginPot = 'inkwell-books/languages/inkwell-books.pot';
const pluginOutput = fs.readFileSync(pluginPot, 'utf8').replace(/\n+$/, '\n');
fs.writeFileSync(pluginPot, pluginOutput);
