const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

// Função para criar diretório recursivamente
function mkdirRecursive(targetDir) {
    const sep = path.sep;
    const initDir = path.isAbsolute(targetDir) ? sep : '';
    const baseDir = __dirname;

    targetDir.split(sep).reduce((parentDir, childDir) => {
        const curDir = path.resolve(baseDir, parentDir, childDir);
        try {
            if (!fs.existsSync(curDir)) {
                fs.mkdirSync(curDir);
            }
        } catch (err) {
            if (err.code !== 'EEXIST') {
                throw err;
            }
        }
        return curDir;
    }, initDir);
}

// Criar pasta dist-theme se não existir
const distPath = path.join(__dirname, '..', 'dist-theme');
mkdirRecursive(distPath);

// Atualizar lista de arquivos e pastas para incluir apenas os que existem
const filesAndFoldersToInclude = [
    'css',
    'js',          
    'img',         
    'inc',
    'style.css',
    'screenshot.png',
    'functions.php',
    'header.php',
    'footer.php',
    'index.php',
    'page.php',
    'single.php'
];

// Função para copiar pasta recursivamente
function copyFolderSync(from, to) {
    const targetDir = path.resolve(to);
    mkdirRecursive(targetDir);
    
    if (fs.existsSync(from)) {
        fs.readdirSync(from).forEach(element => {
            const source = path.join(from, element);
            const dest = path.join(targetDir, element);
            
            if (fs.lstatSync(source).isFile()) {
                fs.copyFileSync(source, dest);
            } else {
                copyFolderSync(source, dest);
            }
        });
    }
}

// Adicionar depois das importações
function checkThemeStructure() {
    const requiredFiles = [
        'style.css',
        'index.php',
        'functions.php'
    ];

    const missingFiles = requiredFiles.filter(file => 
        !fs.existsSync(path.resolve(__dirname, '..', file))
    );

    if (missingFiles.length > 0) {
        console.error('❌ Arquivos obrigatórios faltando:', missingFiles.join(', '));
        process.exit(1);
    }

    console.log('✅ Estrutura básica do tema OK');
}

// Adicionar antes de começar a cópia
checkThemeStructure();

// Copiar arquivos e pastas
filesAndFoldersToInclude.forEach(item => {
    const source = path.resolve(__dirname, '..', item);
    const dest = path.resolve(__dirname, '..', 'dist-theme', item);
    
    try {
        if (fs.existsSync(source)) {
            if (fs.lstatSync(source).isDirectory()) {
                copyFolderSync(source, dest);
            } else {
                const destDir = path.dirname(dest);
                mkdirRecursive(destDir);
                fs.copyFileSync(source, dest);
            }
        } else {
            console.log(`⚠️ Aviso: ${item} não encontrado`);
        }
    } catch (err) {
        console.error(`❌ Erro ao copiar ${item}: ${err.message}`);
    }
});

// Copiar arquivos PHP
try {
    fs.readdirSync(path.join(__dirname, '..'))
        .filter(file => file.endsWith('.php'))
        .forEach(file => {
            const source = path.resolve(__dirname, '..', file);
            const dest = path.resolve(__dirname, '..', 'dist-theme', file);
            fs.copyFileSync(source, dest);
        });
} catch (err) {
    console.error('❌ Erro ao copiar arquivos PHP:', err.message);
}

// Atualizar versão no style.css
try {
    const stylePath = path.resolve(__dirname, '..', 'style.css');
    if (fs.existsSync(stylePath)) {
        const styleContent = fs.readFileSync(stylePath, 'utf8');
        const newVersion = process.env.npm_package_version;
        const updatedStyle = styleContent.replace(/Version: .*/, `Version: ${newVersion}`);
        const destStyle = path.resolve(__dirname, '..', 'dist-theme', 'style.css');
        const destDir = path.dirname(destStyle);
        mkdirRecursive(destDir);
        fs.writeFileSync(destStyle, updatedStyle);
    }
} catch (err) {
    console.error('❌ Erro ao atualizar style.css:', err.message);
}

console.log('✨ Tema preparado para deploy em dist-theme/');

function createZip() {
    const output = fs.createWriteStream(path.resolve(__dirname, '..', 'vime-theme.zip'));
    const archive = archiver('zip', {
        zlib: { level: 9 } // Compressão máxima
    });

    output.on('close', () => {
        console.log('📦 Tema compactado com sucesso: vime-theme.zip');
    });

    archive.on('error', (err) => {
        throw err;
    });

    archive.pipe(output);
    archive.directory('dist-theme/', false);
    archive.finalize();
}

// Chamar após a cópia dos arquivos
createZip(); 