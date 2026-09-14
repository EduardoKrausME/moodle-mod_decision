// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * report.js
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(["jquery", "core/ajax", "core/str"], function($, Ajax, Str) {
    const POLL_INTERVAL = 4000;

    const renderBars = function(root, data) {
        data.options.forEach(function(option) {
            const row = root.find('[data-option-id="' + option.id + '"]');
            row.find('[data-region="count"]').text(option.count);
            row.find('[data-region="percent"]').text(option.percent);
            row.find('[data-region="bar"]').css("width", option.percent + "%");
            row.find(".progress").attr("aria-valuenow", option.percent);
        });
    };

    const renderPie = function(root, data) {
        const pie = root.find('[data-region="pie"]');
        const legend = root.find('[data-region="pie-legend"]');
        const palette = ["#0f6cbf", "#198754", "#fd7e14", "#6f42c1", "#dc3545", "#20c997", "#6c757d", "#d63384"];
        let start = 0;
        const segments = [];
        legend.empty();

        data.options.forEach(function(option, index) {
            const end = start + option.percent;
            const color = palette[index % palette.length];
            if (option.percent > 0) {
                segments.push(color + " " + start + "% " + end + "%");
            }
            const item = $("<div>").addClass("decision-pie-legend-item");
            $("<span>").addClass("decision-pie-swatch").css("background-color", color).appendTo(item);
            $("<span>").text(option.text + " - " + option.count + " (" + option.percent + "%)").appendTo(item);
            legend.append(item);
            start = end;
        });

        pie.css("background", segments.length ? "conic-gradient(" + segments.join(",") + ")" : "var(--bs-gray-200)");
    };

    const renderUsers = function(root, data) {
        const body = root.find('[data-region="response-users"]');
        if (!body.length) {
            return;
        }
        const optionMap = {};
        data.options.forEach(function(option) {
            optionMap[option.id] = option.text;
        });
        body.empty();
        data.users.forEach(function(user) {
            const row = $("<tr>").attr("data-response-id", user.id);
            $("<td>").text(user.fullname).appendTo(row);
            $("<td>").text(optionMap[user.optionid] || "").appendTo(row);
            $("<td>").text(user.timeformatted).appendTo(row);
            body.append(row);
        });
    };

    const refresh = function(root, cmid) {
        Ajax.call([{
            methodname: "mod_decision_get_results",
            args: {cmid: cmid},
        }])[0].done(function(data) {
            root.find('[data-region="total"]').text(data.total);
            renderBars(root, data);
            renderPie(root, data);
            renderUsers(root, data);
        }).fail(function() {
            Str.get_string("refreshfailed", "decision").done(function(message) {
                root.attr("data-refresh-error", message);
            });
        });
    };

    const setChart = function(root, type) {
        root.find('[data-region="bar-chart"]').toggleClass("d-none", type !== "bar");
        root.find('[data-region="pie-chart"]').toggleClass("d-none", type !== "pie");
    };

    return {
        init: function(cmid, initialType) {
            const root = $('[data-region="decision-report"]');
            if (!root.length) {
                return;
            }
            setChart(root, initialType);
            root.find('[data-region="chart-type"]').on("change", function() {
                setChart(root, $(this).val());
            });
            refresh(root, cmid);
            window.setInterval(function() {
                refresh(root, cmid);
            }, POLL_INTERVAL);
        },
    };
});
