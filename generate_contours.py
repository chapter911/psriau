import json
import urllib.request
import urllib.parse
import math
import os
import sys
import ssl
import time

# Bypass SSL verification to avoid CERTIFICATE_VERIFY_FAILED on macOS
ssl._create_default_https_context = ssl._create_unverified_context



def get_bbox(geojson_path):
    with open(geojson_path, 'r') as f:
        data = json.load(f)
    
    min_lon = 180
    max_lon = -180
    min_lat = 90
    max_lat = -90
    
    for feature in data.get('features', []):
        geom = feature.get('geometry', {})
        gtype = geom.get('type')
        coords = geom.get('coordinates', [])
        
        def process_polygon(poly_coords):
            nonlocal min_lon, max_lon, min_lat, max_lat
            for ring in poly_coords:
                for pt in ring:
                    lon, lat = pt[0], pt[1]
                    if lon < min_lon: min_lon = lon
                    if lon > max_lon: max_lon = lon
                    if lat < min_lat: min_lat = lat
                    if lat > max_lat: max_lat = lat

        if gtype == 'Polygon':
            process_polygon(coords)
        elif gtype == 'MultiPolygon':
            for poly in coords:
                process_polygon(poly)
                
    return min_lon, max_lon, min_lat, max_lat

def fetch_elevation_grid(min_lon, max_lon, min_lat, max_lat, nx=40, ny=40):
    print(f"Generating grid: {nx}x{ny} points...")
    lons = [min_lon + i * (max_lon - min_lon) / (nx - 1) for i in range(nx)]
    lats = [min_lat + j * (max_lat - min_lat) / (ny - 1) for j in range(ny)]
    
    points = []
    for lat in lats:
        for lon in lons:
            points.append((lat, lon))
            
    elevations = [0.0] * len(points)
    chunk_size = 100
    total_points = len(points)
    
    print(f"Fetching elevation for {total_points} points from Open-Meteo...")
    
    for start_idx in range(0, total_points, chunk_size):
        end_idx = min(start_idx + chunk_size, total_points)
        chunk = points[start_idx:end_idx]
        
        lat_str = ",".join(f"{p[0]:.6f}" for p in chunk)
        lon_str = ",".join(f"{p[1]:.6f}" for p in chunk)
        
        url = f"https://api.open-meteo.com/v1/elevation?latitude={lat_str}&longitude={lon_str}"
        
        for attempt in range(4):
            try:
                req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
                with urllib.request.urlopen(req, timeout=15) as response:
                    res_data = json.loads(response.read().decode('utf-8'))
                    elev = res_data.get('elevation', [])
                    for i, val in enumerate(elev):
                        elevations[start_idx + i] = val if val is not None else 0.0
                break
            except Exception as e:
                if "HTTP Error 429" in str(e):
                    sleep_time = 10 * (attempt + 1)
                    print(f"\nRate limited. Waiting {sleep_time} seconds before retry...")
                    time.sleep(sleep_time)
                else:
                    if attempt < 3:
                        time.sleep(3)
                    else:
                        print(f"\nError fetching chunk {start_idx}-{end_idx}: {e}")
        
        time.sleep(2.0)
            
        sys.stdout.write(f"\rProgress: {end_idx}/{total_points} points fetched.")
        sys.stdout.flush()
    print("\nFetch completed.")
    
    # Restructure into 2D grid: grid[y][x]
    grid = []
    for j in range(ny):
        row = []
        for i in range(nx):
            idx = j * nx + i
            row.append(elevations[idx])
        grid.append(row)
        
    return lons, lats, grid

def interpolate_grid(lons, lats, grid, factor=3):
    # Bilinear interpolation to make contours smoother
    nx = len(lons)
    ny = len(lats)
    new_nx = (nx - 1) * factor + 1
    new_lat_count = (ny - 1) * factor + 1
    
    new_lons = [lons[0] + i * (lons[-1] - lons[0]) / (new_nx - 1) for i in range(new_nx)]
    new_lats = [lats[0] + j * (lats[-1] - lats[0]) / (new_lat_count - 1) for j in range(new_lat_count)]
    
    new_grid = []
    for j in range(new_lat_count):
        row = []
        y_val = j / factor
        y0 = math.floor(y_val)
        y1 = min(y0 + 1, ny - 1)
        dy = y_val - y0
        
        for i in range(new_nx):
            x_val = i / factor
            x0 = math.floor(x_val)
            x1 = min(x0 + 1, nx - 1)
            dx = x_val - x0
            
            # 4 corners
            v00 = grid[y0][x0]
            v10 = grid[y0][x1]
            v01 = grid[y1][x0]
            v11 = grid[y1][x1]
            
            # Interpolate
            val = (1 - dx) * (1 - dy) * v00 + dx * (1 - dy) * v10 + (1 - dx) * dy * v01 + dx * dy * v11
            row.append(val)
        new_grid.append(row)
        
    return new_lons, new_lats, new_grid

def marching_squares(lons, lats, grid, level):
    segments = []
    ny = len(lats)
    nx = len(lons)
    
    for j in range(ny - 1):
        for i in range(nx - 1):
            # Corners
            x0, x1 = lons[i], lons[i+1]
            y0, y1 = lats[j], lats[j+1]
            
            v00 = grid[j][i]      # Bottom-left (relative)
            v10 = grid[j][i+1]    # Bottom-right
            v11 = grid[j+1][i+1]  # Top-right
            v01 = grid[j+1][i]    # Top-left
            
            # 4-bit configuration index
            cfg = 0
            if v01 >= level: cfg |= 8
            if v11 >= level: cfg |= 4
            if v10 >= level: cfg |= 2
            if v00 >= level: cfg |= 1
            
            if cfg == 0 or cfg == 15:
                continue
                
            # Interpolation helper
            def lerp(val0, val1, coord0, coord1):
                if abs(val1 - val0) < 1e-6:
                    return 0.5 * (coord0 + coord1)
                return coord0 + (level - val0) / (val1 - val0) * (coord1 - coord0)
                
            # Midpoints
            left = (x0, lerp(v00, v01, y0, y1))
            right = (x1, lerp(v10, v11, y0, y1))
            bottom = (lerp(v00, v10, x0, x1), y0)
            top = (lerp(v01, v11, x0, x1), y1)
            
            # Determine segments based on configuration
            if cfg == 1 or cfg == 14:
                segments.append((bottom, left))
            elif cfg == 2 or cfg == 13:
                segments.append((bottom, right))
            elif cfg == 3 or cfg == 12:
                segments.append((left, right))
            elif cfg == 4 or cfg == 11:
                segments.append((top, right))
            elif cfg == 5 or cfg == 10: # Saddle point cases
                segments.append((bottom, right))
                segments.append((top, left))
            elif cfg == 6 or cfg == 9:
                segments.append((bottom, top))
            elif cfg == 7 or cfg == 8:
                segments.append((top, left))
                
    return segments

def build_contour_geojson(lons, lats, grid, levels):
    features = []
    for lvl in levels:
        segments = marching_squares(lons, lats, grid, lvl)
        if not segments:
            continue
            
        # Group segments into continuous lines if possible, or just export as MultiLineString
        # MultiLineString is easier and completely valid
        lines = []
        for seg in segments:
            lines.append([seg[0], seg[1]])
            
        feature = {
            "type": "Feature",
            "geometry": {
                "type": "MultiLineString",
                "coordinates": lines
            },
            "properties": {
                "elevation": lvl,
                "label": f"{lvl}m"
            }
        }
        features.append(feature)
        
    return {
        "type": "FeatureCollection",
        "features": features
    }

def main():
    geojson_path = "public/geojson/kab_rokan_hilir_kecamatan.json"
    output_path = "public/geojson/rokan_hilir_kontur.json"
    
    if not os.path.exists(geojson_path):
        print(f"Error: {geojson_path} not found.")
        sys.exit(1)
        
    print(f"Reading boundary from {geojson_path}...")
    min_lon, max_lon, min_lat, max_lat = get_bbox(geojson_path)
    print(f"Bounding Box: Lon ({min_lon:.5f} to {max_lon:.5f}), Lat ({min_lat:.5f} to {max_lat:.5f})")
    
    # Query elevation
    # Use 15x15 base grid (225 points, only 3 API requests of 100 points)
    lons, lats, grid = fetch_elevation_grid(min_lon, max_lon, min_lat, max_lat, nx=15, ny=15)
    
    # Interpolate to 90x90 for smooth lines (factor = 6)
    print("Interpolating grid for smooth contours...")
    lons_smooth, lats_smooth, grid_smooth = interpolate_grid(lons, lats, grid, factor=6)
    
    # Determine levels
    flat_grid = [val for row in grid for val in row]
    min_elev = min(flat_grid)
    max_elev = max(flat_grid)
    print(f"Elevation Range: {min_elev:.1f}m to {max_elev:.1f}m")
    
    # Define contour levels every 5 meters
    start_level = math.ceil(min_elev / 5.0) * 5
    end_level = math.floor(max_elev / 5.0) * 5
    levels = list(range(int(start_level), int(end_level) + 1, 5))
    if not levels:
        levels = [int((min_elev + max_elev) / 2)]
        
    print(f"Contour levels to generate: {levels}")
    
    contour_geojson = build_contour_geojson(lons_smooth, lats_smooth, grid_smooth, levels)
    
    with open(output_path, 'w') as f:
        json.dump(contour_geojson, f, indent=2)
        
    print(f"Contour GeoJSON written to {output_path}")

if __name__ == "__main__":
    main()
