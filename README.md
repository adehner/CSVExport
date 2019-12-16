CSV Export (plugin for Omeka)
=============================

Allows [Omeka] users to export item metadata to a CSV (comma separated values) file, mapping elements to CSV column data. Each row in the file represents metadata for a single item. This plugin is useful for exporting data from an Omeka site and editing that data in a spreadsheet or OpenRefine. By using Daniel Berthereau's <a href="https://github.com/Daniel-KM/CsvImportPlus">CSV Import+ plugin</a>, the metadata can be imported back into Omeka, overlaying existing Omeka item metadata. When an Omeka record is edited with these batch processes, the existing reference URL and record ID for an item remain unchanged.

Instructions
------------

Refer to Omeka’s Installing Plugins and Themes <a href="https://vimeo.com/153819886">screencast</a> and <a href="http://omeka.org/codex/Managing_Plugins_2.0">written documentation</a> for step-by-step instructions on installing Omeka plugins.

The CSV Export plugin exports a batch of Omeka item records as a .csv file. By default, the plugin exports all Dublin Core metadata, contained in these records. However, you may select additional element sets for export from this plugin's configuration page:

![Alt text](/csvexport_options.png?raw=true)

### To export all item records in your Omeka repository, follow the steps below:

![Alt text](/csvexport_all.png?raw=true)

1. Login to your Omeka dashboard.
2. Click on “CSV Export” in the left sidebar, and the CSV Export page will open.
3. Click the “Export all data as CSV” button, and a .csv file of item records will download to your desktop.
4. Make a copy of the .csv file as a backup, “just in case.” Name the copy whatever you like.

### To export a subset of your Omeka item records, follow the steps below:

![Alt text](/csvexport_browse.png?raw=true)

1. Login to your Omeka dashboard.
2. Use the dashboard’s Advanced Search * form to define a subset of item records (eg. a single collection’s item records).
3. The subset of item records will be listed in a Browse Items page like the screenshot below. When you have the subset you want, scroll to the bottom of the page and click the “Export results as CSV” button. A .csv file of item records will download to your desktop.
4. Make a copy of the .csv file as a backup, “just in case.” Name the copy whatever you like.

Known Issues & Plans for Improvement
------------------------------------

Currently a subset can only be exported if it's created via the Advanced Search form. Browsing, by clicking “Tags” or “Collections” in the left sidebar, and filtering (with a Quick Filter) will not work as expected. I'm hoping to expand the subset export functionality to recognize subsets created by browsing and filtering.

I'm planning to build in options to identify individual metadata fields for export via Omeka's Dashboard.

This plugin doesn’t use jobs. For big databases, increase server max time and memory size.

Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.

Troubleshooting
---------------

See online issues on the [plugin issues] page on GitHub.

Copyright
---------

* Copyright Anneliese Dehner, 2017
* Copyright Daniel Berthereau, 2019 (see [Daniel-KM] on GitHub)

[Omeka]: https://omeka.org/classic
[plugin issues]:  https://github.com/adehner/CSVExport/issues
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
