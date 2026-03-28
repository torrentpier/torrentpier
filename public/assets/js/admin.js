/*
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2026 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

document.addEventListener('DOMContentLoaded', function () {

  // Sidebar section accordion
  document.addEventListener('click', function (e) {
    var heading = e.target.closest('[data-toggle-section]');
    if (!heading) return;

    e.preventDefault();
    heading.closest('.admin-sidebar-section').classList.toggle('open');
  });

});
