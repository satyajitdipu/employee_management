#!/usr/bin/env python3
import cv2
import argparse
import subprocess
import numpy as np
import os
import tempfile

def detect_and_center_head(image_path, output_path):
    # Load the cascade classifier for detecting faces
    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

    # Load the image
    img = cv2.imread(image_path, cv2.IMREAD_UNCHANGED)  # Make sure we're preserving any alpha channel

    # If the image is 3-channel (no alpha), we add an alpha channel
    if img.shape[2] == 3:
        img = cv2.cvtColor(img, cv2.COLOR_BGR2BGRA)

    gray = cv2.cvtColor(img, cv2.COLOR_BGRA2GRAY)

    # Detect the face in the image
    faces = face_cascade.detectMultiScale(gray, 1.3, 5)

    if len(faces) == 0:
        print("No face detected!")
        return

    # For simplicity, we'll consider only the first detected face
    x, y, w, h = faces[0]

    # Get the center of the face
    face_center = (x + w//2, y + h//2)

    # Calculate the translation needed to center the face
    height, width, _ = img.shape
    dx = width//2 - face_center[0]
    dy = height//2 - face_center[1]

    # Create the translation matrix
    M = np.float32([[1, 0, dx], [0, 1, dy]])

    # Translate the image to center the face
    centered_img = cv2.warpAffine(img, M, (width, height), flags=cv2.INTER_LINEAR, borderMode=cv2.BORDER_CONSTANT, borderValue=(0,0,0,0))
    # The borderValue=(0,0,0,0) makes sure that the new regions in the image are transparent.

    # Save the output image in PNG format to preserve transparency
    cv2.imwrite(output_path, centered_img)

def resize_and_center_head(image_path, output_path, head_to_face_ratio=1.5):
    # Load the image and the face detector
    image = cv2.imread(image_path, cv2.IMREAD_UNCHANGED)

    # Ensure the image has an alpha channel for transparency
    if image.shape[2] == 3:
        image = cv2.cvtColor(image, cv2.COLOR_BGR2BGRA)

    face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

    # Detect faces
    faces = face_cascade.detectMultiScale(image, 1.1, 4)
    if len(faces) == 0:
        print("No face detected in the image.")
        return

    # For simplicity, consider the first detected face
    x, y, w, h = faces[0]

    # Estimate the head height to be 1.5 times the face height
    estimated_head_height = int(head_to_face_ratio * h)
    delta = (estimated_head_height - h) // 2

    # Adjust y coordinate based on the estimated head dimensions
    y = y - delta

    # Clip values to stay within image bounds
    y = max(0, y)
    h = min(image.shape[0] - y, estimated_head_height)
    w = min(image.shape[1] - x, w)

    # Calculate the new cropping window dimensions
    target_img_height = int(1.43 * estimated_head_height)
    target_img_width = int(1.021 * estimated_head_height)

    # Calculate cropping window's upper left corner position
    start_y = y - int(0.15 * estimated_head_height)
    start_x = max(0, x - (target_img_width - w) // 2)

    # Adjust end points if they go beyond image dimensions
    end_y = min(image.shape[0], start_y + target_img_height)
    end_x = min(image.shape[1], start_x + target_img_width)

    # Ensure start_y remains non-negative
    start_y = max(0, start_y)

    # Crop the original image based on the calculated window
    cropped_img = image[start_y:end_y, start_x:end_x]

    # Save the processed image
    cv2.imwrite(output_path, cropped_img)

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Crop the image based on head position and size.')
    parser.add_argument('-i', '--input', required=True, help='Path to input image')
    parser.add_argument('-o', '--output', required=True, help='Path to save processed image')
    parser.add_argument('-r', '--head_to_face_ratio', type=float, default=1.5, required=False, help='Ratio of Head Size to the Face Size')
    args = parser.parse_args()

    temp_png_file_0 = tempfile.mktemp(suffix='.png')
    temp_png_file_1 = tempfile.mktemp(suffix='.png')

    subprocess.run(["carvekit", "-i", args.input, "-o", f"{temp_png_file_0}"], check=True)

    detect_and_center_head(temp_png_file_0, temp_png_file_1)

    resize_and_center_head(temp_png_file_1, args.output, args.head_to_face_ratio)

    os.remove(temp_png_file_0)
    os.remove(temp_png_file_1)
