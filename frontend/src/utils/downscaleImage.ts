/**
 * Shrink a camera photo before it leaves the phone.
 *
 * A phone shoots ~4MB at 4000px wide. The model reads nothing extra from those
 * pixels, so the only thing the difference buys is an upload the user watches
 * on kitchen wifi. Nothing here is load-bearing: if any step throws, the
 * original file is handed back and the scan still works.
 */
export interface PreparedPhoto {
    blob: Blob;
    /** Object URL for the preview — the caller owns it and must revoke it. */
    previewUrl: string;
    /** False when the original was returned unchanged. */
    resized: boolean;
}

const MAX_EDGE = 1200;
const QUALITY = 0.8;

export const preparePhoto = async (file: File): Promise<PreparedPhoto> => {
    try {
        // "from-image" applies the EXIF rotation while decoding, so a photo
        // taken in portrait does not reach the model lying on its side.
        const bitmap = await createImageBitmap(file, {imageOrientation: "from-image"});

        const scale = Math.min(1, MAX_EDGE / Math.max(bitmap.width, bitmap.height));
        const width = Math.round(bitmap.width * scale);
        const height = Math.round(bitmap.height * scale);

        const canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;

        const context = canvas.getContext("2d");

        if (context === null) throw new Error("No 2d canvas context.");

        context.drawImage(bitmap, 0, 0, width, height);
        bitmap.close();

        const blob = await new Promise<Blob | null>((resolve) => {
            canvas.toBlob(resolve, "image/jpeg", QUALITY);
        });

        if (blob === null) throw new Error("Canvas produced no blob.");

        return {blob, previewUrl: URL.createObjectURL(blob), resized: true};
    } catch {
        return {blob: file, previewUrl: URL.createObjectURL(file), resized: false};
    }
};

export const formatBytes = (bytes: number): string => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};
