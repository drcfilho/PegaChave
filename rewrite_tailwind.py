import os
import re

files = [
    r'C:\xampp\htdocs\Projeto\pegachave\src\Views\admin_chaves.php',
    r'C:\xampp\htdocs\Projeto\pegachave\src\Views\admin_reservas.php',
    r'C:\xampp\htdocs\Projeto\pegachave\src\Views\admin_restricoes.php',
    r'C:\xampp\htdocs\Projeto\pegachave\src\Views\admin_consulta.php',
    r'C:\xampp\htdocs\Projeto\pegachave\src\Views\admin_gerar_qr.php',
    r'C:\xampp\htdocs\Projeto\pegachave\src\Views\admin_config.php'
]

def process_tbody(match):
    tbody = match.group(0)
    def process_tr(m_tr):
        tr = m_tr.group(0)
        if 'class="' in tr:
            return tr.replace('class="', 'class="hover:bg-slate-50 transition-colors ')
        else:
            return tr.replace('<tr', '<tr class="hover:bg-slate-50 transition-colors"')
    return re.sub(r'<tr[^>]*>', process_tr, tbody)

for file_path in files:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # 1. preflight: true
    content = re.sub(r'preflight:\s*false', 'preflight: true', content)

    # 2. Delete <style>...</style>
    content = re.sub(r'<style>[\s\S]*?<\/style>\s*', '', content)

    # 3. body
    content = re.sub(r'<body[^>]*>', '<body class="bg-slate-50 text-slate-800 font-sans flex min-h-screen">', content)

    # 4. main
    content = re.sub(r'<main(.*?)>', r'<main\1 class="flex-1 ml-0 lg:ml-[260px] p-6 md:p-10 transition-all duration-300">', content)

    # 5. Tables
    content = content.replace('<table>', '<table class="w-full text-left border-collapse">')
    content = content.replace('<thead>', '<thead class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs uppercase tracking-wider">')
    content = re.sub(r'(<tbody>[\s\S]*?<\/tbody>)', process_tbody, content)

    # 5. Cards
    content = content.replace('class="content-card"', 'class="content-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5"')
    content = content.replace('class="list-card"', 'class="list-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden px-6 py-5"')

    # 5. Buttons
    content = content.replace('class="btn-add"', 'class="btn-add bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors"')
    content = content.replace('class="btn-generate"', 'class="btn-generate bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors"')
    content = content.replace('class="btn-save"', 'class="btn-save bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors"')

    # 5. Inputs
    content = content.replace('class="form-control"', 'class="form-control w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"')
    content = content.replace('class="search-input"', 'class="search-input w-full border border-slate-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"')

    # 5. Modals (add flex-col or standard classes, and hidden [&.active]:flex to hide/show)
    content = content.replace('class="modal"', 'class="modal hidden [&.active]:flex items-center justify-center fixed inset-0 bg-black/50 backdrop-blur-sm z-50"')
    content = content.replace('class="modal-content"', 'class="modal-content bg-white rounded-2xl p-6 shadow-xl w-full max-w-lg"')

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Done")
