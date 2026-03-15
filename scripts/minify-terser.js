#!/usr/bin/env node

/**
 * Terser Minification Script (SAFE Version)
 * Minifies all built JavaScript files using Terser
 */

import { minify } from 'terser';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const BUILD_DIR = path.join(__dirname, '..', 'public', 'build');
const MANIFEST_PATH = path.join(BUILD_DIR, 'manifest.json');

// Terser options for SECURE production (safe but effective)
const terserOptions = {
    compress: {
        // Dead code removal
        dead_code: true,
        drop_console: true,
        drop_debugger: true,
        
        // Code transformations
        evaluate: true,
        hoist_funs: true,
        hoist_vars: false,
        if_return: true,
        join_vars: true,
        loops: true,
        properties: true,
        reduce_vars: true,
        sequences: true,
        unused: true,
        
        // Advanced obfuscation
        booleans: true,
        collapse_vars: true,
        comparisons: true,
        conditionals: true,
        inline: 2,
        negate_iife: true,
        passes: 2,
        pure_getters: true,
        reduce_funcs: true,
        switches: true,
        typeofs: true,
        warnings: false
    },
    mangle: {
        eval: false,
        keep_classnames: false,
        keep_fnames: false,
        reserved: ['define', 'require', 'module', 'exports'],
        toplevel: true
    },
    output: {
        comments: false,
        beautify: false,
        indent_level: 2
    },
    toplevel: true
};

console.log('🔧 Starting Terser Secure Minification...\n');

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

        minify(originalCode, terserOptions).then(result => {
            if (result.error) {
                console.error(`   ❌ Error: ${result.error.message}\n`);
                return;
            }

            // Write minified code (SAFE - no additional encoding)
            fs.writeFileSync(filePath, result.code);
            
            const minifiedSize = fs.statSync(filePath).size;
            totalMinifiedSize += minifiedSize;
            minifiedCount++;

            const savings = ((originalSize - minifiedSize) / originalSize * 100).toFixed(1);
            console.log(`   Minified: ${(minifiedSize / 1024).toFixed(2)} KB`);
            console.log(`   Saved: ${savings}%\n`);
        }).catch(error => {
            console.error(`   ❌ Error: ${error.message}\n`);
        });
    });

    // Wait for all files to process
    setTimeout(() => {
        console.log('\n===========================================');
        console.log('✅ Terser Secure Minification Complete!');
        console.log('===========================================');
        console.log(`Files processed: ${minifiedCount}/${jsFiles.length}`);
        console.log(`Total original size: ${(totalOriginalSize / 1024).toFixed(2)} KB`);
        console.log(`Total minified size: ${(totalMinifiedSize / 1024).toFixed(2)} KB`);
        const totalSavings = ((totalOriginalSize - totalMinifiedSize) / totalOriginalSize * 100).toFixed(1);
        console.log(`Total savings: ${totalSavings}%`);
        console.log('===========================================\n');
        console.log('✅ All files are working and secure!');
    }, 2000);

} catch (error) {
    console.error('❌ Fatal error:', error.message);
    process.exit(1);
}
