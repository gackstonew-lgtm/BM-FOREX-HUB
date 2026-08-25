import os
import difflib

dir1 = r"c:\Users\Dan\Documents\BM forex"
dir2 = r"C:\Users\Dan\Documents\BM Forex Gackstone"

files = [
    "index.php",
    "admin/index.php",
    "admin/js/admin.js"
]

diff_output = []

for f in files:
    path1 = os.path.join(dir1, f)
    path2 = os.path.join(dir2, f)
    
    if not os.path.exists(path2):
        diff_output.append(f"=== File {f} does not exist in Gackstone folder ===\n\n")
        continue
        
    with open(path1, "r", encoding="utf-8", errors="ignore") as f1, open(path2, "r", encoding="utf-8", errors="ignore") as f2:
        lines1 = f1.readlines()
        lines2 = f2.readlines()
        
    diff = list(difflib.unified_diff(
        lines2, lines1, 
        fromfile=f"Gackstone/{f}", 
        tofile=f"Workspace/{f}", 
        n=2
    ))
    
    if diff:
        diff_output.append(f"=== Diff for {f} (Workspace is tofile (+), Gackstone is fromfile (-)) ===\n")
        diff_output.extend(diff)
        diff_output.append("\n\n")
    else:
        diff_output.append(f"=== {f} is identical between folders ===\n\n")

output_path = os.path.join(r"C:\Users\Dan\.gemini\antigravity\brain\8efe7ecd-247f-4aa7-823b-be18d822ad26", "diff_results.txt")
with open(output_path, "w", encoding="utf-8") as out:
    out.writelines(diff_output)

print(f"Diff completed. Results written to {output_path}")
