#!/usr/bin/env python3
import argparse
import os
import base64
import subprocess
from passport_photo_creator import detect_and_center_head, resize_and_center_head
import tempfile
from PIL import Image

def is_valid_image(image_path):
    """Check if the given path is a valid image file."""
    try:
        with Image.open(image_path) as img:
            img.verify()
        return True
    except (IOError, SyntaxError):
        return False

def replace_in_file(filename, old_string, new_string):
    with open(filename, 'r') as file:
        file_data = file.read()
        file_data = file_data.replace(old_string, new_string)

    with open(filename, 'w') as file:
        file.write(file_data)

def main():
    parser = argparse.ArgumentParser(description='Process inputs.')
    parser.add_argument('-t', '--template', required=True, help='Path to template.')
    parser.add_argument('-n', '--name', required=True, help='Employee Name.')
    parser.add_argument('-e', '--code', required=True, help='Employee Code.')
    parser.add_argument('-b', '--blood_group', required=True, help='Blood Group.')
    parser.add_argument('-i', '--image', required=True, help='Path to Employee Image.')
    parser.add_argument('-y', '--experience', required=True, type=float, help='Years of Experience.')
    parser.add_argument('-r', '--head_to_face_ratio', type=float, default=1.5, required=False, help='Ratio of Head Size to the Face Size')
    parser.add_argument('-o', '--output', required=True, help='Output PDF File Path.')

    args = parser.parse_args()

    if not os.path.exists(args.image) or not is_valid_image(args.image):
        raise ValueError("The provided image path either does not exist or is not a valid image file.")

    temp_png_file_0 = tempfile.mktemp(suffix='.png')
    temp_png_file_1 = tempfile.mktemp(suffix='.png')
    temp_png_file_2 = tempfile.mktemp(suffix='.png')

    subprocess.run(["carvekit", "-i", args.image, "-o", f"{temp_png_file_0}"], check=True)

    detect_and_center_head(temp_png_file_0, temp_png_file_1)
    resize_and_center_head(temp_png_file_1, temp_png_file_2, args.head_to_face_ratio)

    temp_svg = tempfile.mktemp(suffix='.svg')
    temp_pdf = tempfile.mktemp(suffix='.pdf')

    # Copy the template SVG
    os.system(f'cp {args.template}/Front.svg {temp_svg}')

    # Replace placeholders
    replace_in_file(temp_svg, "FirstName LastName", args.name)
    replace_in_file(temp_svg, "NTE-000", args.code)
    replace_in_file(temp_svg, "Blood Group: BG", f"Blood Group: {args.blood_group}")

    # Resize image using mogrify
    os.system(f'mogrify -resize 1200x1680 {temp_png_file_2}')

    # Convert image to base64
    with open(temp_png_file_2, "rb") as image_file:
        image_base64 = base64.b64encode(image_file.read()).decode()

    replace_in_file(temp_svg, "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNc+R8AAlcBqugYjdEAAAAASUVORK5CYII=", image_base64)

    # Replace color based on years of experience
    if 0 <= args.experience <= 1.25:
        color = "0068FF"
    elif 1.25 < args.experience <= 2.5:
        color = "B2B2B2"
    elif 2.5 < args.experience <= 5:
        color = "CC0000"
    elif 5 < args.experience <= 8.5:
        color = "D8C5A3"
    else:
        color = "000000"

    replace_in_file(temp_svg, "12b72b", color)

    # Convert SVG to PDF using Inkscape
    os.system(f'inkscape {temp_svg} --export-type=pdf --export-filename={temp_pdf}')

    # Combine the two PDFs
    os.system(f'pdftk {temp_pdf} {args.template}/Back.pdf cat output {args.output}')
    print(f"Process complete. Output available at: {args.output}")

    # Cleanup temp files
    os.remove(temp_svg)
    os.remove(temp_pdf)

    os.remove(temp_png_file_0)
    os.remove(temp_png_file_1)
    os.remove(temp_png_file_2)

if __name__ == "__main__":
    main()
