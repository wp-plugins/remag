def build_sources
  system "coffee -co build src/coffee"
  system "eco -o build/templates src/eco"
  
  compiled_sources = Dir['build/**/*'].select { |f| File.file? f }
  compiled_sources << compiled_sources.delete('build/admin.js')
  IO.write 'admin/assets/js/admin.js', compiled_sources.map { |f| IO.read f }.join("\n\n")
end