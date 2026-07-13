import shapefile
import json
import os
import math

shp_path = "public/shp/kab_rokan_hilir/KONTUR_LN_50K.shp"
geojson_dir = "public/geojson"
geojson_path = os.path.join(geojson_dir, "rokan_hilir_kontur.json")

os.makedirs(geojson_dir, exist_ok=True)

print(f"Reading shapefile: {shp_path}")
sf = shapefile.Reader(shp_path)

fields = [f[0] for f in sf.fields[1:]]
valknt_idx = fields.index("VALKNT") if "VALKNT" in fields else -1
namobj_idx = fields.index("NAMOBJ") if "NAMOBJ" in fields else -1

# Simplify points using radial distance method
def simplify_line(pts, tolerance=0.00025):
    if len(pts) < 3:
        return [[round(p[0], 5), round(p[1], 5)] for p in pts]
    
    # Keep the first point
    simplified = [[round(pts[0][0], 5), round(pts[0][1], 5)]]
    tol_sq = tolerance * tolerance
    
    for i in range(1, len(pts) - 1):
        pt = pts[i]
        last_kept = simplified[-1]
        
        dx = pt[0] - last_kept[0]
        dy = pt[1] - last_kept[1]
        
        if (dx*dx + dy*dy) >= tol_sq:
            simplified.append([round(pt[0], 5), round(pt[1], 5)])
            
    # Always keep the last point
    last_pt = [round(pts[-1][0], 5), round(pts[-1][1], 5)]
    if last_pt != simplified[-1]:
        simplified.append(last_pt)
        
    return simplified

features = []
for i, shape_record in enumerate(sf.shapeRecords()):
    shape = shape_record.shape
    record = shape_record.record
    
    elevation = float(record[valknt_idx]) if valknt_idx != -1 else 0.0
    name = str(record[namobj_idx]) if namobj_idx != -1 else ""
    
    properties = {
        "elevation": elevation,
        "name": name
    }
    
    points = shape.points
    parts = list(shape.parts) + [len(points)]
    
    if len(parts) <= 2:
        # Single LineString
        coords = simplify_line(points)
        geometry = {
            "type": "LineString",
            "coordinates": coords
        }
    else:
        # MultiLineString
        coords = []
        for j in range(len(parts) - 1):
            start = parts[j]
            end = parts[j+1]
            simplified_part = simplify_line(points[start:end])
            if len(simplified_part) >= 2:
                coords.append(simplified_part)
        
        if not coords:
            continue
            
        geometry = {
            "type": "MultiLineString",
            "coordinates": coords
        }
        
    feature = {
        "type": "Feature",
        "geometry": geometry,
        "properties": properties
    }
    features.append(feature)

geojson_data = {
    "type": "FeatureCollection",
    "features": features
}

print(f"Writing GeoJSON to: {geojson_path}")
with open(geojson_path, "w", encoding="utf-8") as f:
    # Save space by omitting whitespace indentations
    json.dump(geojson_data, f, separators=(',', ':'))

print("Conversion complete!")
print(f"Total features converted: {len(features)}")
print(f"Output file size: {os.path.getsize(geojson_path) / (1024*1024):.2f} MB")
