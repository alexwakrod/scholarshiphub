<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trusted International Scholarship Source URLs
    |--------------------------------------------------------------------------
    |
    | These URLs are used by the AI‑powered scraping job to discover
    | scholarships from reliable aggregators. All sources are:
    |   - Focused on international students
    |   - Serve static HTML (compatible with your scraper)
    |   - Actively maintained with thousands of opportunities
    |
    | Sources compiled from multiple trusted scholarship directories.
    | Updated: 2026-07-04
    |
    */
    'default' => [

        // ═══════════════════════════════════════════════════════════
        //  MAJOR GLOBAL AGGREGATORS
        // ═══════════════════════════════════════════════════════════

        'https://www.scholars4dev.com/',
        'https://www.scholarshiptab.com/',
        'https://www.scholarshiproar.com/',
        'https://www.internationalscholarships.com',          // Comprehensive listing of grants & scholarships[reference:0][reference:1]
        'https://www.iefa.org',                               // Premier database for international students[reference:2][reference:3][reference:4]
        'https://globalscholarships.com',                     // 4,500+ scholarships, filter by degree/nationality[reference:5][reference:6]
        'https://www.scholarships.com',                       // 3.7M+ scholarships[reference:7][reference:8]
        'https://www.fastweb.com',                            // 1.5M+ scholarships, $3.4B in total[reference:9][reference:10]
        'https://www.scholarshipportal.com',                  // 1,000+ scholarships in Europe & beyond[reference:11]
        'https://www.studyportals.com/scholarships',          // Comprehensive scholarship finder[reference:12][reference:13]
        'https://www.petersons.com/scholarship-search.aspx',  // US & international students[reference:14]

        // ═══════════════════════════════════════════════════════════
        //  GRADUATE & PHD FOCUS
        // ═══════════════════════════════════════════════════════════

        'https://www.findaphd.com',                           // PhD positions worldwide[reference:15][reference:16][reference:17]
        'https://www.phdportal.com',                          // PhD programs & scholarships[reference:18]
        'https://www.mastersportal.com/scholarships',         // Master's programs & funding[reference:19]
        'https://www.gograd.org',                             // Graduate scholarships & grants[reference:20]
        'https://www.postgraduate-funding.com',               // Alternative funding for postgrads[reference:21]
        'https://www.profellow.com',                          // Fellowships & fully funded graduate programs[reference:22]
        'https://www.phdpositions.eu',                        // PhD vacancies in Europe[reference:23]
        'https://www.scholarshippositions.com',               // Scholarships & funded positions[reference:24]

        // ═══════════════════════════════════════════════════════════
        //  GOVERNMENT & OFFICIAL PROGRAMMES
        // ═══════════════════════════════════════════════════════════

        'https://www2.daad.de/deutschland/stipendium/datenbank/en/21148-scholarship-database/', // DAAD[reference:25]
        'https://fulbright.state.gov',                        // US flagship exchange programme
        'https://www.chevening.org',                          // UK government scholarships
        'https://erasmus-plus.ec.europa.eu/opportunities/opportunities-for-individuals/students/erasmus-mundus-joint-masters-scholarships', // EU-funded[reference:26]
        'https://www.euraxess.eu',                            // EU research positions & funding[reference:27][reference:28]
        'https://www.britishcouncil.org/study-work-abroad/scholarships', // UK-focused grants[reference:29]

        // ═══════════════════════════════════════════════════════════
        //  ADDITIONAL TRUSTED AGGREGATORS
        // ═══════════════════════════════════════════════════════════

        'https://www.scholarshipdb.net',                      // International scholarship database[reference:30]
        'https://studentscholarships.org',                    // Long‑standing directory
        'https://www.scholarshipowl.com',                     // Personality‑based matching[reference:31][reference:32]
        'https://www.niche.com/colleges/scholarships',        // Scholarship search + college reviews[reference:33]
        'https://www.unigo.com/scholarships',                 // Search engine with recipient advice[reference:34]
        'https://www.cappex.com/scholarships',                // Performance‑based matching[reference:35]
        'https://www.collegeboard.org/scholarship-search',    // 2,200+ programmes[reference:36][reference:37]
        'https://www.chegg.com/scholarships',                 // $1B+ in scholarships[reference:38][reference:39]
        'https://www.appily.com/scholarships',                // Massive database with filters[reference:40]
        'https://www.scholarshippicker.com',                  // Curated international listings
        'https://www.scholarshipmonkey.com',                  // Simple static aggregator
        'https://www.academicpositions.com',                  // Academic jobs & funded research[reference:41][reference:42][reference:43]
        'https://opportunitiescorners.info',                  // Global scholarships & fellowships[reference:44]
        'https://www.globalstudyroad.com',                    // Comprehensive global platform[reference:45][reference:46]
        'https://www.european-funding-guide.eu',              // Scholarships + all funding types[reference:47]
        'https://www.goabroad.com/scholarships',              // Funded graduate programmes[reference:48]
        'https://www.studentscholarshipsearch.com',           // International student search engine
        'https://www.scholarshipscanner.com',                 // UK-focused with clear listings
    ],
];