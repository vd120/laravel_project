#!/usr/bin/env node

/**
 * UglifyJS Minification Script
 * Minifies all built JavaScript files using UglifyJS
 */

import UglifyJS from 'uglify-js';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const BUILD_DIR = path.join(__dirname, '..', 'public', 'build');
const MANIFEST_PATH = path.join(BUILD_DIR, 'manifest.json');

// UglifyJS options for production
const uglifyOptions = {
    compress: {
        dead_code: true,
        drop_console: false,
        drop_debugger: true,
        evaluate: true,
        hoist_funs: true,
        if_return: true,
        join_vars: true,
        loops: true,
        properties: true,
        sequences: true,
        unused: true,
        warnings: false
    },
    mangle: {
        eval: false,
        keep_classnames: false,
        keep_fnames: false,
        reserved: [],
        toplevel: true
    },
    output: {
        comments: false,
        beautify: false
    }
};

console.log('🔧 Starting UglifyJS Minification...\n');

let minifiedCount = 0;
let totalOriginalSize = 0;
let totalMinifiedSize = 0;

try {
    const manifest = JSON.parse(fs.readFileSync(MANIFEST_PATH, 'utf8'));
    const jsFiles = Object.values(manifest)
        .filter(entry => entry.file && entry.file.endsWith('.js'))
        .map(entry => entry.file);

    console.log(`Found ${jsFiles.length} JavaScript files to minify\n`);

    jsFiles.forEach(file => {
        const filePath = path.join(BUILD_DIR, file);
        
        if (!fs.existsSync(filePath)) {
            console.warn(`⚠️  File not found: ${file}`);
            return;
        }

        const originalCode = fs.readFileSync(filePath, 'utf8');
        const originalSize = fs.statSync(filePath).size;
        totalOriginalSize += originalSize;

        console.log(`🔧 Minifying: ${file}`);
        console.log(`   Original: ${(originalSize / 1024).toFixed(2)} KB`);

        try {
            // UglifyJS doesn't support ES6+ by default, use ecma: 2020
            const result = UglifyJS.minify(originalCode, {
                ...uglifyOptions,
                output: { ...uglifyOptions.output, ecma: 2020 }
            });

            if (result.error) {
                console.error(`   ❌ Error: ${result.error}\n`);
                return;
            }

            // Write minified code
            fs.writeFileSync(filePath, result.code);
            
            const minifiedSize = fs.statSync(filePath).size;
            totalMinifiedSize += minifiedSize;
            minifiedCount++;

            const savings = ((originalSize - minifiedSize) / originalSize * 100).toFixed(1);
            console.log(`   Minified: ${(minifiedSize / 1024).toFixed(2)} KB`);
            console.log(`   Saved: ${savings}%\n`);
        } catch (error) {
            console.error(`   ❌ Error: ${error.message}\n`);
        }
    });

    console.log('\n===========================================');
    console.log('✅ UglifyJS Minification Complete!');
    console.log('===========================================');
    console.log(`Files processed: ${minifiedCount}/${jsFiles.length}`);
    console.log(`Total original size: ${(totalOriginalSize / 1024).toFixed(2)} KB`);
    console.log(`Total minified size: ${(totalMinifiedSize / 1024).toFixed(2)} KB`);
    const totalSavings = ((totalOriginalSize - totalMinifiedSize) / totalOriginalSize * 100).toFixed(1);
    console.log(`Total savings: ${totalSavings}%`);
    console.log('===========================================\n');

} catch (error) {
    console.error('❌ Fatal error:', error.message);
    process.exit(1);
}
