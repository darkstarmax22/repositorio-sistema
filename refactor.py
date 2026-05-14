import os
import re

components_dir = r"c:\xampp\htdocs\repositorio\sistema\resources\views\components"
livewire_views_dir = r"c:\xampp\htdocs\repositorio\sistema\resources\views\livewire"
livewire_classes_dir = r"c:\xampp\htdocs\repositorio\sistema\app\Livewire"

os.makedirs(livewire_views_dir, exist_ok=True)
os.makedirs(livewire_classes_dir, exist_ok=True)

for filename in os.listdir(components_dir):
    if not filename.endswith(".blade.php") or filename == "sidebar.blade.php":
        continue

    filepath = os.path.join(components_dir, filename)
    with open(filepath, 'r', encoding='utf-8-sig') as f:
        content = f.read()

    # Find the PHP block anywhere
    match = re.search(r'<\?php(.*?)\?>\s*(.*)', content, re.DOTALL)
    if not match:
        print(f"Skipping {filename} - no PHP block")
        continue

    php_code = match.group(1)
    blade_code = match.group(2)

    if 'extends Component' not in php_code:
        print(f"Skipping {filename} - not a Volt component")
        continue

    # Convert kebab-case to PascalCase
    component_name = filename.replace('.blade.php', '')
    class_name = ''.join(word.capitalize() for word in component_name.split('-'))

    # Build new PHP code
    lines = php_code.strip().split('\n')
    new_php_lines = ['<?php', '', 'namespace App\\Livewire;', '']
    
    in_class = False
    for line in lines:
        if 'new class extends Component' in line:
            new_php_lines.append(f"class {class_name} extends Component")
            in_class = True
        elif line.strip() == '};':
            new_php_lines.append(f"    public function render()")
            new_php_lines.append(f"    {{")
            new_php_lines.append(f"        return view('livewire.{component_name}');")
            new_php_lines.append(f"    }}")
            new_php_lines.append("}")
        else:
            new_php_lines.append(line)

    new_php_code = '\n'.join(new_php_lines)

    # Save class
    class_filepath = os.path.join(livewire_classes_dir, f"{class_name}.php")
    with open(class_filepath, 'w', encoding='utf-8') as f:
        f.write(new_php_code)
    
    # Save blade
    view_filepath = os.path.join(livewire_views_dir, filename)
    with open(view_filepath, 'w', encoding='utf-8') as f:
        f.write(blade_code.strip() + '\n')
    
    # Delete old file
    os.remove(filepath)
    print(f"Migrated {filename} -> {class_name}.php & {filename}")

print("Refactoring complete.")
