#!/usr/bin/env node

/**
 * Post-build JavaScript Obfuscation Script
 * Obfuscates all built JavaScript files for production
 */

import JavaScriptObfuscator from 'javascript-obfuscator';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const BUILD_DIR = path.join(__dirname, '..', 'public', 'build', 'assets');
const MANIFEST_PATH = path.join(__dirname, '..', 'public', 'build', 'manifest.json');

// Obfuscation options for production
const obfuscationOptions = {
    compact: true,
    controlFlowFlattening: true,
    controlFlowFlatteningThreshold: 0.75,
    deadCodeInjection: true,
    deadCodeInjectionThreshold: 0.4,
    disableConsoleOutput: true,
    identifierNamesGenerator: 'hexadecimal',
    log: false,
    numbersToExpressions: true,
    renameGlobals: false,
    rotateStringArray: true,
    selfDefending: true,
    simplify: true,
    splitStrings: true,
    splitStringsChunkLength: 10,
    stringArray: true,
    stringArrayCallsTransform: true,
    stringArrayCallsTransformThreshold: 0.5,
    stringArrayEncoding: ['base64'],
    stringArrayIndexShift: true,
    stringArrayRotate: true,
    stringArrayShuffle: true,
    stringArrayWrappersCount: 2,
    stringArrayWrappersChainedCalls: true,
    stringArrayWrappersParametersMaxCount: 2,
    stringArrayWrappersType: 'variable',
    stringArrayThreshold: 0.75,
    transformObjectKeys: true,
    unicodeEscapeSequence: false
};

console.log('🔒 Starting JavaScript Obfuscation...\n');

// Read manifest to get all JS files
let obfuscatedCount = 0;
let totalSize = 0;
let obfuscatedSize = 0;

try {
    const manifest = JSON.parse(fs.readFileSync(MANIFEST_PATH, 'utf8'));
    const jsFiles = Object.values(manifest)
        .filter(entry => entry.file && entry.file.endsWith('.js'))
        .map(entry => entry.file);

    console.log(`Found ${jsFiles.length} JavaScript files to obfuscate\n`);

    jsFiles.forEach(file => {
        const filePath = path.join(__dirname, '..', 'public', 'build', file);
        
        if (!fs.existsSync(filePath)) {
            console.warn(`⚠️  File not found: ${file}`);
            return;
        }

        const originalCode = fs.readFileSync(filePath, 'utf8');
        const originalSize = fs.statSync(filePath).size;
        totalSize += originalSize;

        console.log(`🔒 Obfuscating: ${file}`);
        console.log(`   Original size: ${(originalSize / 1024).toFixed(2)} KB`);

        try {
            // Obfuscate the code
            const obfuscationResult = JavaScriptObfuscator.obfuscate(originalCode, obfuscationOptions);
            const obfuscatedCode = obfuscationResult.getObfuscatedCode();
            
            // Write obfuscated code back to file
            fs.writeFileSync(filePath, obfuscatedCode);
            
            const newSize = fs.statSync(filePath).size;
            obfuscatedSize += newSize;
            obfuscatedCount++;

            console.log(`   Obfuscated size: ${(newSize / 1024).toFixed(2)} KB`);
            console.log(`   Size change: ${((newSize - originalSize) / originalSize * 100).toFixed(1)}%\n`);
        } catch (error) {
            console.error(`   ❌ Error obfuscating ${file}: ${error.message}\n`);
        }
    });

    console.log('\n===========================================');
    console.log('✅ Obfuscation Complete!');
    console.log('===========================================');
    console.log(`Files processed: ${obfuscatedCount}/${jsFiles.length}`);
    console.log(`Total original size: ${(totalSize / 1024).toFixed(2)} KB`);
    console.log(`Total obfuscated size: ${(obfuscatedSize / 1024).toFixed(2)} KB`);
    console.log(`Overall size change: ${((obfuscatedSize - totalSize) / totalSize * 100).toFixed(1)}%`);
    console.log('===========================================\n');

    console.log('⚠️  Note: Obfuscated code is harder to read but still executable.');
    console.log('   For maximum security, also enable server-side validation.\n');

} catch (error) {
    console.error('❌ Fatal error:', error.message);
    console.error('\nMake sure to run "npm run build" first to generate the manifest.');
    process.exit(1);
}
