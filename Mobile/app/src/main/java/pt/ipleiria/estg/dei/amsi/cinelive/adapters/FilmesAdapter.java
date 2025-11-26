package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.util.ArrayList;
import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemFilmeBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Filme;

public class FilmesAdapter extends RecyclerView.Adapter<FilmesAdapter.ViewHolder> {

    private final List<Filme> filmesOriginais;
    private List<Filme> filmesVisiveis;
    private final OnFilmeClickListener listener;

    // Notifica o fragment quando um filme é escolhido
    public interface OnFilmeClickListener {
        void onFilmeSelected(Filme filme);
    }

    public FilmesAdapter(List<Filme> filmes, OnFilmeClickListener listener) {
        this.filmesOriginais = filmes;
        this.filmesVisiveis = filmes;
        this.listener = listener;
    }

    public void filtrar(String q) {
        String query = q.toLowerCase();

        filmesVisiveis = new ArrayList<>();
        for (Filme filme : filmesOriginais) {
            if (filme.titulo.toLowerCase().contains(query)) {
                filmesVisiveis.add(filme);
            }
        }

        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemFilmeBinding binding = ItemFilmeBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false
        );
        return new ViewHolder(binding);
    }

    // Associa os dados do cinema à posição correspondente no RecyclerView.
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Filme filme = filmesVisiveis.get(position);

        holder.binding.tvTitulo.setText(filme.titulo);

        Glide.with(holder.itemView.getContext())
                .load(filme.posterUrl)
                .placeholder(R.drawable.poster_placeholder)
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .into(holder.binding.ivPoster);

        holder.itemView.setOnClickListener(v -> listener.onFilmeSelected(filme));
    }

    @Override
    public int getItemCount() {
        return filmesVisiveis.size();
    }

    // O ViewHolder representa 1 item da lista
    static class ViewHolder extends RecyclerView.ViewHolder {
        ItemFilmeBinding binding;

        public ViewHolder(ItemFilmeBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}